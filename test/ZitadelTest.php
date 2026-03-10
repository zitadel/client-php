<?php

namespace Zitadel\Client\Test;

use Docker\Docker;
use Docker\API\Model\NetworksCreatePostBody;
use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;
use Testcontainers\Wait\WaitForHostPort;
use Zitadel\Client\Auth\NoAuthAuthenticator;
use Zitadel\Client\TransportOptions;
use Zitadel\Client\Zitadel;

class ZitadelTest extends TestCase
{
    protected static string $host;
    protected static int $httpPort;
    protected static int $httpsPort;
    protected static int $proxyPort;
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
        self::$networkId = $response->getId();

        self::$wiremock = (new GenericContainer("wiremock/wiremock:3.12.1"))
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

        self::$proxy = (new GenericContainer("ubuntu/squid:6.10-24.10_beta"))
            ->withNetwork(self::$networkName)
            ->withMount($fixturesDir . '/squid.conf', '/etc/squid/squid.conf')
            ->withExposedPorts(3128)
            ->start();

        self::$host = self::$wiremock->getHost();
        self::$httpPort = self::$wiremock->getMappedPort(8080);
        self::$httpsPort = self::$wiremock->getMappedPort(8443);
        self::$proxyPort = self::$proxy->getMappedPort(3128);

        (new WaitForHostPort())
            ->wait(self::$proxy);

        (new WaitForHttp(self::$httpPort))
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait(self::$wiremock);
    }

    public static function tearDownAfterClass(): void
    {
        self::$proxy?->stop();
        self::$wiremock?->stop();
        if (self::$networkId !== null) {
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
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(caCertPath: self::$caCertPath),
        );

        $response = $zitadel->settings->getGeneralSettings();
        $this->assertEquals('https', $response->getDefaultLanguage());
    }

    public function testInsecureMode(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(insecure: true),
        );

        $response = $zitadel->settings->getGeneralSettings();
        $this->assertEquals('https', $response->getDefaultLanguage());
    }

    public function testDefaultHeaders(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "http://" . self::$host . ":" . self::$httpPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(defaultHeaders: ["X-Custom-Header" => "test-value"]),
        );

        $response = $zitadel->settings->getGeneralSettings();
        $this->assertEquals('http', $response->getDefaultLanguage());
        $this->assertEquals('test-value', $response->getDefaultOrgId());
    }

    public function testProxyUrl(): void
    {
        $zitadel = Zitadel::withAccessToken(
            "http://wiremock:8080",
            "test-token",
            new TransportOptions(proxyUrl: "http://" . self::$host . ":" . self::$proxyPort),
        );

        $response = $zitadel->settings->getGeneralSettings();
        $this->assertEquals('http', $response->getDefaultLanguage());
    }

    public function testNoCaCertFails(): void
    {
        $this->expectException(Exception::class);
        Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
        );
    }
}
