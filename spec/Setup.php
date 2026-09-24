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
use Testcontainers\Wait\WaitForHostPort;
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

        new WaitForHostPort()
            ->withTimeout(60000)
            ->wait(self::$proxy);

        putenv('PROXY_URL=http://' . self::$proxy->getHost() . ':' . self::$proxy->getMappedPort(3128));
        putenv('PROXY_AUTH_URL=http://' . self::$proxy->getHost() . ':' . self::$proxy->getMappedPort(3129));
        putenv('CHASM_INTERNAL_HTTP_URL=http://' . self::ORIGIN_ALIAS . ':8080');

        register_shutdown_function($this->stopProxyFixture(...));
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
