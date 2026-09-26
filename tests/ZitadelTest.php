<?php

namespace Zitadel\Client\Test;

use Docker\Docker;
use Docker\API\Model\ContainersIdJsonGetResponse200;
use Docker\API\Model\Mount;
use Docker\API\Model\MountTmpfsOptions;
use Docker\API\Model\NetworksCreatePostBody;
use Docker\API\Model\NetworksCreatePostResponse201;
use Docker\API\Model\PortBinding;
use HaydenPierce\ClassFinder\ClassFinder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\NoAuthAuthenticator;
use Zitadel\Client\Auth\PersonalAccessTokenAuthenticator;
use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\Errors\NetworkException;
use Zitadel\Client\TransportOptions;
use Zitadel\Client\Zitadel;

class ZitadelTest extends TestCase
{
    protected static string $host;
    protected static int $httpPort;
    protected static int $httpsPort;
    protected static int $proxyPort;
    protected static int $proxyAuthPort;
    protected static string $caCertPath;
    private static ?string $networkId = null;
    private static ?string $networkName = null;
    private static ?StartedGenericContainer $wiremock = null;
    private static ?StartedGenericContainer $proxy = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $fixturesDir = __DIR__ . '/fixtures';
        self::$caCertPath = $fixturesDir . '/ca.pem';

        $docker = Docker::create();
        self::$networkName = 'zitadel-test-' . bin2hex(random_bytes(4));
        $networkBody = new NetworksCreatePostBody();
        $networkBody->setName(self::$networkName);
        $response = $docker->networkCreate($networkBody);
        self::assertInstanceOf(NetworksCreatePostResponse201::class, $response);
        self::$networkId = $response->getId();

        $wiremock = new GenericContainer("wiremock/wiremock:3.12.1")
            ->withName('wiremock')
            ->withNetwork(self::$networkName)
            ->withCommand([
                "--https-port", "8443",
                "--https-keystore", "/home/wiremock/keystore.p12",
                "--keystore-password", "password",
                "--keystore-type", "PKCS12",
                "--global-response-templating",
            ])
            ->withMount($fixturesDir . '/keystore.p12', '/home/wiremock/keystore.p12')
            ->withMount($fixturesDir . '/mappings', '/home/wiremock/mappings')
            ->withExposedPorts(8080, 8443)
            ->start();
        self::$wiremock = $wiremock;

        /* ubuntu/squid declares VOLUME /var/log/squid and /var/spool/squid, and
         * squid drops to the unprivileged `proxy` user before it opens its logs.
         * The other SDKs mount both as writable tmpfs (mode 1777) so the log
         * daemon can start; without them squid never finishes booting on CI and
         * the auth port never opens. testcontainers-php has no tmpfs helper, so
         * the container is extended with one to stay aligned with the siblings. */
        $proxy = new class ("ubuntu/squid:6.10-24.10_beta") extends GenericContainer {
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

        self::$host = $wiremock->getHost();
        self::$httpPort = $wiremock->getMappedPort(8080);
        self::$httpsPort = $wiremock->getMappedPort(8443);

        /* 3128 is the open proxy; 3129 the same proxy gated by Basic credentials. */
        [self::$proxyPort, self::$proxyAuthPort] = self::awaitProxyPorts($proxy, self::$host);

        new WaitForHttp(8080, 60000)
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait($wiremock);
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
    private static function awaitProxyPorts(StartedGenericContainer $proxy, string $host, int $timeoutMs = 60000): array
    {
        $id = $proxy->getId();
        $deadline = microtime(true) + ($timeoutMs / 1000);
        $diag = 'no attempt completed';

        do {
            try {
                $fresh = new StartedGenericContainer($id);
                $open = $fresh->getMappedPort(3128);
                $auth = $fresh->getMappedPort(3129);
                $openOk = self::isTcpPortOpen($host, $open);
                $authOk = self::isTcpPortOpen($host, $auth);
                if ($openOk && $authOk) {
                    return [$open, $auth];
                }
                $diag = sprintf(
                    'host=%s 3128->%d(open=%s) 3129->%d(open=%s)',
                    $host,
                    $open,
                    $openOk ? 'yes' : 'no',
                    $auth,
                    $authOk ? 'yes' : 'no',
                );
            } catch (\Throwable $e) {
                $diag = 'getMappedPort threw: ' . $e->getMessage();
            }
            usleep(200 * 1000);
        } while (microtime(true) < $deadline);

        throw new \RuntimeException(
            "Squid proxy ports 3128/3129 did not become available in time. Last state: {$diag}. "
            . "Raw inspect: " . self::dumpPorts($id) . ". Container logs:\n"
            . $proxy->logs()
        );
    }

    private static function dumpPorts(string $id): string
    {
        $inspect = Docker::create()->containerInspect($id);
        if (!$inspect instanceof ContainersIdJsonGetResponse200) {
            $type = is_object($inspect) ? $inspect::class : gettype($inspect);
            return "inspect returned {$type}";
        }

        $status = $inspect->getState()?->getStatus() ?? 'unknown';
        $ports = $inspect->getNetworkSettings()?->getPorts() ?? [];
        $detail = [];
        foreach ($ports as $key => $bindings) {
            $binding = $bindings[0] ?? null;
            $hostPort = $binding instanceof PortBinding ? ($binding->getHostPort() ?? 'null') : 'no-binding';
            $detail[] = "{$key}=>{$hostPort}";
        }

        return "status={$status} ports=[" . implode(' ', $detail) . "]";
    }

    private static function isTcpPortOpen(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($connection !== false) {
            fclose($connection);
            return true;
        }

        return false;
    }

    public static function tearDownAfterClass(): void
    {
        self::$proxy?->stop();
        self::$wiremock?->stop();
        if (self::$networkId !== null && self::$networkName !== null) {
            Docker::create()->networkDelete(self::$networkName);
        }
        parent::tearDownAfterClass();
    }

    public function testServicesDynamic(): void
    {
        $expected = ClassFinder::getClassesInNamespace('Zitadel\Client\Api');
        $expected = array_filter($expected, fn (string $class): bool => str_ends_with($class, 'ServiceApi'));
        sort($expected);

        $zitadel = new Zitadel(new NoAuthAuthenticator());
        $reflection = new ReflectionClass($zitadel);
        $properties = $reflection->getProperties();
        $actual = [];
        foreach ($properties as $prop) {
            $type = $prop->getType();
            if ($type instanceof ReflectionNamedType && str_starts_with($type->getName(), 'Zitadel\Client\Api\\')) {
                $actual[] = $type->getName();
            }
        }
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function testCustomCaCert(): void
    {
        $transport = new TransportOptions(caCertPath: self::$caCertPath);
        $zitadel = Zitadel::withAuthenticator(
            ClientCredentialsAuthenticator::builder(
                "https://" . self::$host . ":" . self::$httpsPort,
                "dummy-client",
                "dummy-secret",
            )->build(),
            $transport,
        );

        $response = $zitadel->settingsService->getGeneralSettings(new \stdClass());
        $this->assertEquals('https', $response->defaultLanguage);
    }

    public function testInsecureMode(): void
    {
        $transport = new TransportOptions(verifySsl: false);
        $zitadel = Zitadel::withAuthenticator(
            ClientCredentialsAuthenticator::builder(
                "https://" . self::$host . ":" . self::$httpsPort,
                "dummy-client",
                "dummy-secret",
            )->build(),
            $transport,
        );

        $response = $zitadel->settingsService->getGeneralSettings(new \stdClass());
        $this->assertEquals('https', $response->defaultLanguage);
    }

    public function testDefaultHeaders(): void
    {
        $transport = new TransportOptions(defaultHeaders: ["X-Custom-Header" => "test-value"]);
        $zitadel = Zitadel::withAuthenticator(
            ClientCredentialsAuthenticator::builder(
                "http://" . self::$host . ":" . self::$httpPort,
                "dummy-client",
                "dummy-secret",
            )->build(),
            $transport,
        );

        $response = $zitadel->settingsService->getGeneralSettings(new \stdClass());
        $this->assertEquals('http', $response->defaultLanguage);
        $this->assertEquals('test-value', $response->defaultOrgId);
    }

    public function testProxyUrl(): void
    {
        $zitadel = Zitadel::withAuthenticator(
            new PersonalAccessTokenAuthenticator(
                "http://wiremock:8080",
                "test-token",
            ),
            new TransportOptions(proxy: "http://" . self::$host . ":" . self::$proxyPort),
        );

        $response = $zitadel->settingsService->getGeneralSettings(new \stdClass());
        $this->assertEquals('http', $response->defaultLanguage);
    }

    public function testProxyRequiresCredentials(): void
    {
        $zitadel = Zitadel::withAuthenticator(
            new PersonalAccessTokenAuthenticator(
                "http://wiremock:8080",
                "test-token",
            ),
            new TransportOptions(proxy: "http://" . self::$host . ":" . self::$proxyAuthPort),
        );

        try {
            $zitadel->settingsService->getGeneralSettings(new \stdClass());
            $this->fail('Expected the credentialed proxy to reject the request with 407');
        } catch (ApiException $e) {
            $this->assertSame(407, $e->getCode());
        }
    }

    public function testProxyWithCredentials(): void
    {
        $zitadel = Zitadel::withAuthenticator(
            new PersonalAccessTokenAuthenticator(
                "http://wiremock:8080",
                "test-token",
            ),
            new TransportOptions(proxy: "http://user:pass@" . self::$host . ":" . self::$proxyAuthPort),
        );

        $response = $zitadel->settingsService->getGeneralSettings(new \stdClass());
        $this->assertEquals('http', $response->defaultLanguage);
    }

    public function testNoCaCertFails(): void
    {
        $zitadel = Zitadel::withAuthenticator(
            ClientCredentialsAuthenticator::builder(
                "https://" . self::$host . ":" . self::$httpsPort,
                "dummy-client",
                "dummy-secret",
            )->build(),
        );
        try {
            $zitadel->settingsService->getGeneralSettings(new \stdClass());
            $this->fail('Expected NetworkException');
        } catch (NetworkException $e) {
            $this->assertSame(NetworkException::class, $e::class);
        }
    }
}
