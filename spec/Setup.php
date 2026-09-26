<?php

declare(strict_types=1);

namespace Zitadel\Client\Spec;

use Docker\API\Model\NetworksCreatePostBody;
use Docker\API\Model\NetworksCreatePostResponse201;
use Docker\Docker;
use Dotenv\Dotenv;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;

/**
 * PHPUnit extension that:
 * 1. Loads environment variables from .env files
 * 2. Enhances JUnit XML output with timestamp, hostname, and warnings attributes
 * 3. Brings up the proxy-auth fixture the generated transport tests expect
 */
final class Setup implements Extension
{
    /** Network alias the proxy resolves the origin by. */
    private const string ORIGIN_ALIAS = 'proxy-origin';

    private static ?StartedGenericContainer $proxy = null;

    private static ?StartedGenericContainer $origin = null;

    private static ?string $networkName = null;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $dotenv = Dotenv::createImmutable(getcwd() ?: '.');
        $dotenv->safeLoad();

        new JUnitReporter('build/reports/junit-raw.xml', 'build/reports/junit.xml');

        $this->startProxyFixture();
    }

    /**
     * Starts the Squid proxy and the origin it forwards to, and publishes
     * their addresses as environment variables.
     *
     * The generated transport tests read `PROXY_AUTH_URL` (a proxy port that
     * answers 407 unless the request carries Basic proxy credentials) and
     * `CHASM_INTERNAL_HTTP_URL` (an origin reachable from inside the proxy's
     * network). They are bootstrap-time values because the tests are plain
     * functions with no per-class fixture of their own.
     */
    private function startProxyFixture(): void
    {
        if (getenv('PROXY_AUTH_URL') !== false) {
            return;
        }

        $fixturesDir = dirname(__DIR__) . '/tests/fixtures';

        $docker = Docker::create();
        self::$networkName = 'zitadel-proxy-' . bin2hex(random_bytes(4));
        $networkBody = new NetworksCreatePostBody();
        $networkBody->setName(self::$networkName);
        $network = $docker->networkCreate($networkBody);
        if (!$network instanceof NetworksCreatePostResponse201) {
            throw new \RuntimeException('Could not create the proxy fixture network');
        }

        /* The origin carries the alias the proxy resolves, so the request the
         * test makes is proxied rather than served over the host bridge. */
        self::$origin = new GenericContainer('wiremock/wiremock:3.12.1')
            ->withNetwork(self::$networkName)
            ->withAliases([self::ORIGIN_ALIAS])
            ->withMount($fixturesDir . '/mappings', '/home/wiremock/mappings')
            ->withExposedPorts(8080)
            ->start();

        self::$proxy = new GenericContainer('ubuntu/squid:6.10-24.10_beta')
            ->withNetwork(self::$networkName)
            ->withMount($fixturesDir . '/squid.conf', '/etc/squid/squid.conf')
            ->withExposedPorts(3128, 3129)
            ->start();

        new WaitForHttp(8080, 60000)
            ->withPath('/__admin/mappings')
            ->withExpectedStatusCode(200)
            ->wait(self::$origin);

        /* Both proxy ports must be published AND accepting connections before
         * getMappedPort() is read. A single generic host-port wait settles as
         * soon as the first binding it happens to see is open, so in CI the
         * container inspect can still be missing 3129 when its mapped port is
         * read, and getMappedPort(3129) returns '' and aborts the whole suite.
         * Wait for both 3128 and 3129 explicitly instead. */
        $this->waitForMappedPorts(self::$proxy, [3128, 3129]);

        putenv('PROXY_URL=http://' . self::$proxy->getHost() . ':' . self::$proxy->getMappedPort(3128));
        putenv('PROXY_AUTH_URL=http://' . self::$proxy->getHost() . ':' . self::$proxy->getMappedPort(3129));
        putenv('CHASM_INTERNAL_HTTP_URL=http://' . self::ORIGIN_ALIAS . ':8080');

        register_shutdown_function($this->stopProxyFixture(...));
    }

    /**
     * Blocks until every given container port is both published by Docker and
     * accepting TCP connections on the host, or a timeout elapses.
     *
     * The container's inspect response is polled through a fresh client so the
     * started container's own cached inspect is not populated from a snapshot
     * taken before the later ports were bound; once this returns, reading each
     * `getMappedPort()` is safe.
     *
     * @param int[] $ports
     */
    private function waitForMappedPorts(StartedGenericContainer $proxy, array $ports, int $timeoutMs = 60000): void
    {
        $docker = Docker::create();
        $id = $proxy->getId();
        $host = $proxy->getHost();
        $deadline = microtime(true) + ($timeoutMs / 1000);

        do {
            $inspect = $docker->containerInspect($id);
            $bound = $inspect?->getNetworkSettings()?->getPorts() ?? [];

            $ready = true;
            foreach ($ports as $port) {
                $binding = $bound["{$port}/tcp"][0] ?? null;
                $hostPort = $binding?->getHostPort();
                if ($hostPort === null || $hostPort === '' || !$this->isTcpPortOpen($host, (int) $hostPort)) {
                    $ready = false;
                    break;
                }
            }

            if ($ready) {
                return;
            }

            usleep(200 * 1000);
        } while (microtime(true) < $deadline);

        throw new \RuntimeException('Proxy ports ' . implode(', ', $ports) . ' did not become available in time');
    }

    private function isTcpPortOpen(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($connection !== false) {
            fclose($connection);
            return true;
        }

        return false;
    }

    /**
     * Tears the fixture down. Every step is guarded: the runtime is already
     * shutting down, so a dead Docker socket must not turn a passing run into
     * a crashed worker.
     */
    private function stopProxyFixture(): void
    {
        try {
            self::$proxy?->stop();
        } catch (\Throwable) {
            /* ignored */
        }
        try {
            self::$origin?->stop();
        } catch (\Throwable) {
            /* ignored */
        }
        try {
            if (self::$networkName !== null) {
                Docker::create()->networkDelete(self::$networkName);
            }
        } catch (\Throwable) {
            /* ignored */
        }
    }
}
