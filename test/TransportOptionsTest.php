<?php

namespace Zitadel\Client\Test;

use Docker\Docker;
use Docker\API\Model\NetworksCreatePostBody;
use PHPUnit\Framework\TestCase;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;
use Zitadel\Client\TransportOptions;
use Zitadel\Client\Zitadel;

class TransportOptionsTest extends TestCase
{
    protected static string $host;
    protected static int $httpPort;
    protected static int $httpsPort;
    protected static int $proxyPort;
    protected static string $caCertPath;
    private static ?string $networkId = null;
    private static ?StartedGenericContainer $wiremock = null;
    private static ?StartedGenericContainer $proxy = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $fixturesDir = __DIR__ . '/fixtures';
        self::$caCertPath = $fixturesDir . '/ca.pem';

        $docker = Docker::create();
        $networkBody = new NetworksCreatePostBody();
        $networkBody->setName('zitadel-proxy-test');
        $response = $docker->networkCreate($networkBody);
        self::$networkId = $response->getId();

        self::$wiremock = (new GenericContainer("wiremock/wiremock:3.3.1"))
            ->withName('wiremock')
            ->withNetwork('zitadel-proxy-test')
            ->withCommand([
                "--https-port", "8443",
                "--https-keystore", "/home/wiremock/keystore.p12",
                "--keystore-password", "password",
                "--keystore-type", "PKCS12",
                "--global-response-templating",
            ])
            ->withMount($fixturesDir . '/keystore.p12', '/home/wiremock/keystore.p12')
            ->withExposedPorts(8080, 8443)
            ->start();

        self::$proxy = (new GenericContainer("vimagick/tinyproxy"))
            ->withNetwork('zitadel-proxy-test')
            ->withMount($fixturesDir . '/tinyproxy.conf', '/etc/tinyproxy/tinyproxy.conf')
            ->withExposedPorts(8888)
            ->start();

        self::$host = self::$wiremock->getHost();
        self::$httpPort = self::$wiremock->getMappedPort(8080);
        self::$httpsPort = self::$wiremock->getMappedPort(8443);
        self::$proxyPort = self::$proxy->getMappedPort(8888);

        (new WaitForHttp(self::$httpPort))
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait(self::$wiremock);

        self::registerStubs();
    }

    public static function tearDownAfterClass(): void
    {
        self::$proxy?->stop();
        self::$wiremock?->stop();
        if (self::$networkId !== null) {
            Docker::create()->networkDelete('zitadel-proxy-test');
        }
        parent::tearDownAfterClass();
    }

    private static function registerStubs(): void
    {
        $adminUrl = "http://" . self::$host . ":" . self::$httpPort;

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'request' => ['method' => 'GET', 'url' => '/.well-known/openid-configuration'],
                    'response' => [
                        'status' => 200,
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => '{"issuer":"{{request.baseUrl}}","token_endpoint":"{{request.baseUrl}}/oauth/v2/token","authorization_endpoint":"{{request.baseUrl}}/oauth/v2/authorize","userinfo_endpoint":"{{request.baseUrl}}/oidc/v1/userinfo","jwks_uri":"{{request.baseUrl}}/oauth/v2/keys"}',
                    ],
                ]),
            ],
        ]);
        $response = file_get_contents("{$adminUrl}/__admin/mappings", false, $context);
        self::assertNotFalse($response, 'Failed to register WireMock stub');

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'request' => ['method' => 'POST', 'url' => '/oauth/v2/token'],
                    'response' => [
                        'status' => 200,
                        'headers' => ['Content-Type' => 'application/json'],
                        'jsonBody' => [
                            'access_token' => 'test-token-12345',
                            'token_type' => 'Bearer',
                            'expires_in' => 3600,
                        ],
                    ],
                ]),
            ],
        ]);
        $response = file_get_contents("{$adminUrl}/__admin/mappings", false, $context);
        self::assertNotFalse($response, 'Failed to register WireMock stub');

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'request' => ['method' => 'POST', 'url' => '/zitadel.settings.v2.SettingsService/GetGeneralSettings'],
                    'response' => [
                        'status' => 200,
                        'headers' => ['Content-Type' => 'application/json'],
                        'jsonBody' => new \stdClass(),
                    ],
                ]),
            ],
        ]);
        $response = file_get_contents("{$adminUrl}/__admin/mappings", false, $context);
        self::assertNotFalse($response, 'Failed to register WireMock stub');
    }

    public function testCustomCaCert(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(caCertPath: self::$caCertPath),
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
    }

    public function testInsecureMode(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(insecure: true),
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
    }

    public function testDefaultHeaders(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "http://" . self::$host . ":" . self::$httpPort,
            "dummy-client",
            "dummy-secret",
            new TransportOptions(defaultHeaders: ["X-Custom-Header" => "test-value"]),
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);

        $zitadel->settings->getGeneralSettings();

        $verifyContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'url' => '/zitadel.settings.v2.SettingsService/GetGeneralSettings',
                    'headers' => ['X-Custom-Header' => ['equalTo' => 'test-value']],
                ]),
            ],
        ]);
        $result = json_decode(
            file_get_contents(
                "http://" . self::$host . ":" . self::$httpPort . "/__admin/requests/count",
                false,
                $verifyContext
            ),
            true
        );
        $this->assertGreaterThanOrEqual(1, $result['count'], "Custom header should be present on API call");
    }

    public function testProxyUrl(): void
    {
        $zitadel = Zitadel::withAccessToken(
            "http://wiremock:8080",
            "test-token",
            new TransportOptions(proxyUrl: "http://" . self::$host . ":" . self::$proxyPort),
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
        $zitadel->settings->getGeneralSettings();
    }

    public function testNoCaCertFails(): void
    {
        $this->expectException(\Exception::class);
        Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
        );
    }
}
