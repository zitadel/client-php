<?php

namespace Zitadel\Client\Test;

use Docker\Docker;
use Docker\API\Model\NetworksCreatePostBody;
use Docker\API\Model\NetworksCreatePostResponse201;
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

        self::$wiremock = new GenericContainer("wiremock/wiremock:3.12.1")
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

        self::$proxy = new GenericContainer("ubuntu/squid:6.10-24.10_beta")
            ->withNetwork(self::$networkName)
            ->withMount($fixturesDir . '/squid.conf', '/etc/squid/squid.conf')
            ->withExposedPorts(3128, 3129)
            ->start();

        self::$host = self::$wiremock->getHost();
        self::$httpPort = self::$wiremock->getMappedPort(8080);
        self::$httpsPort = self::$wiremock->getMappedPort(8443);

        /* 3128 is the open proxy; 3129 is the same proxy gated by Basic proxy
         * credentials. Both must be published and listening before their mapped
         * ports are read: a generic host-port wait can settle on the first
         * binding it sees, leaving getMappedPort(3129) empty in CI. */
        self::waitForMappedPorts(self::$proxy, [3128, 3129]);
        self::$proxyPort = self::$proxy->getMappedPort(3128);
        self::$proxyAuthPort = self::$proxy->getMappedPort(3129);

        new WaitForHttp(8080, 60000)
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait(self::$wiremock);
    }

    /**
     * Blocks until every given container port is both published by Docker and
     * accepting TCP connections on the host, or a timeout elapses.
     *
     * @param int[] $ports
     */
    private static function waitForMappedPorts(StartedGenericContainer $proxy, array $ports, int $timeoutMs = 60000): void
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
                if ($hostPort === null || $hostPort === '' || !self::isTcpPortOpen($host, (int) $hostPort)) {
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
