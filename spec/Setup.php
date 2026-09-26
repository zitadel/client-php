<?php

declare(strict_types=1);

namespace Zitadel\Client\Spec;

use Docker\API\Model\ContainerConfigExposedPortsItem;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\Mount;
use Docker\API\Model\MountTmpfsOptions;
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

        /* ubuntu/squid declares VOLUME /var/log/squid and /var/spool/squid, and
         * squid drops to the unprivileged `proxy` user before it opens its logs.
         * The other SDKs mount both as writable tmpfs (mode 1777) so the log
         * daemon can start; without them squid never finishes booting on CI and
         * the auth port never opens. testcontainers-php has no tmpfs helper, so
         * the container is extended with one to stay aligned with the siblings. */
        $proxy = new class ('ubuntu/squid:6.10-24.10_beta') extends GenericContainer {
            /** @param array<string, int> $mountsByTarget container path => octal mode */
            public function withTmpfs(array $mountsByTarget): static
            {
                foreach ($mountsByTarget as $target => $mode) {
                    $this->mounts[] = new Mount()
                        ->setType('tmpfs')
                        ->setTarget($target)
                        ->setTmpfsOptions(new MountTmpfsOptions()->setMode($mode));
                }

                return $this;
            }

            protected function createContainerConfig(): ContainersCreatePostBody
            {
                /* testcontainers-php sets host PortBindings but never
                 * Config.ExposedPorts, so Docker only publishes ports the image
                 * already EXPOSEs. ubuntu/squid exposes 3128 but not the 3129 auth
                 * port, so on a strict daemon (CI) the 3129 binding is silently
                 * dropped while a lenient one (local Docker Desktop) still maps it.
                 * Expose every requested port so both are published everywhere. */
                $config = parent::createContainerConfig();
                $exposed = [];
                foreach ($this->exposedPorts as $port) {
                    /* An empty ContainerConfigExposedPortsItem serialises to a
                     * JSON array ([]); the daemon rejects that and wants an object
                     * ({}). A single entry forces object serialisation, and Docker
                     * reads each ExposedPorts value as an empty struct, ignoring
                     * its contents. */
                    $item = new ContainerConfigExposedPortsItem();
                    $item['exposed'] = true;
                    $exposed[$port] = $item;
                }
                $config->setExposedPorts($exposed);

                return $config;
            }
        };

        $proxy = $proxy
            ->withNetwork(self::$networkName)
            ->withMount($fixturesDir . '/squid.conf', '/etc/squid/squid.conf')
            ->withTmpfs(['/var/log/squid' => 0o1777, '/var/spool/squid' => 0o1777])
            ->withExposedPorts(3128, 3129)
            ->start();
        self::$proxy = $proxy;

        new WaitForHttp(8080, 60000)
            ->withPath('/__admin/mappings')
            ->withExpectedStatusCode(200)
            ->wait(self::$origin);

        /* 3128 is the open proxy; 3129 the same proxy gated by Basic credentials. */
        $proxyHost = $proxy->getHost();
        [$proxyPort, $proxyAuthPort] = $this->awaitProxyPorts($proxy, $proxyHost);

        putenv('PROXY_URL=http://' . $proxyHost . ':' . $proxyPort);
        putenv('PROXY_AUTH_URL=http://' . $proxyHost . ':' . $proxyAuthPort);
        putenv('CHASM_INTERNAL_HTTP_URL=http://' . self::ORIGIN_ALIAS . ':8080');

        register_shutdown_function($this->stopProxyFixture(...));
    }

    /**
     * Polls a fresh container inspect until both squid ports are published and
     * accepting TCP connections, returning their mapped host ports.
     *
     * getMappedPort() memoises the first inspect it reads. On CI that snapshot
     * can be taken while the container is already running but before Docker has
     * surfaced the published host ports, so the mapped port stays empty for the
     * rest of the run. A fresh StartedGenericContainer each iteration forces a
     * fresh inspect; squid's own logs are surfaced if the ports never appear.
     *
     * @return array{int, int} the open port and the credentialed port
     */
    private function awaitProxyPorts(StartedGenericContainer $proxy, string $host, int $timeoutMs = 60000): array
    {
        $id = $proxy->getId();
        $deadline = microtime(true) + ($timeoutMs / 1000);

        do {
            try {
                $fresh = new StartedGenericContainer($id);
                $open = $fresh->getMappedPort(3128);
                $auth = $fresh->getMappedPort(3129);
                if ($this->isTcpPortOpen($host, $open) && $this->isTcpPortOpen($host, $auth)) {
                    return [$open, $auth];
                }
            } catch (\RuntimeException) {
                /* a port is not published yet; retry until the deadline */
            }
            usleep(200 * 1000);
        } while (microtime(true) < $deadline);

        throw new \RuntimeException(
            "Squid proxy ports 3128/3129 did not become available in time. Container logs:\n" . $proxy->logs()
        );
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
