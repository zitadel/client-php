<?php

declare(strict_types=1);

namespace Zitadel\Client\Spec;

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

        /* Block until squid has opened the credentialed auth port (3129), not
         * merely until the container is running. Reading getMappedPort() before
         * squid finishes parsing its two-port config caches an inspect snapshot
         * that is missing 3129, which then aborts the whole suite. Waiting on
         * squid's own readiness line makes both mapped ports safe to read, and
         * on timeout the container logs are surfaced so a failure is diagnosable
         * rather than a bare "did not become available". */
        $deadline = microtime(true) + 60.0;
        while (!str_contains($proxy->logs(), 'listening port: authport')) {
            if (microtime(true) > $deadline) {
                throw new \RuntimeException(
                    "Squid proxy did not open its auth port in time. Container logs:\n" . $proxy->logs()
                );
            }
            usleep(200 * 1000);
        }

        putenv('PROXY_URL=http://' . $proxy->getHost() . ':' . $proxy->getMappedPort(3128));
        putenv('PROXY_AUTH_URL=http://' . $proxy->getHost() . ':' . $proxy->getMappedPort(3129));
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
