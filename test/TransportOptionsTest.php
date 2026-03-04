<?php

namespace Zitadel\Client\Test;

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
    protected static string $caCertPath;
    private static ?StartedGenericContainer $wiremock = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $fixturesDir = __DIR__ . '/fixtures';
        self::$caCertPath = $fixturesDir . '/ca.pem';

        self::$wiremock = (new GenericContainer("wiremock/wiremock:3.3.1"))
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

        self::$host = self::$wiremock->getHost();
        self::$httpPort = self::$wiremock->getMappedPort(8080);
        self::$httpsPort = self::$wiremock->getMappedPort(8443);

        (new WaitForHttp(self::$httpPort))
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait(self::$wiremock);

        self::registerStubs();
    }

    public static function tearDownAfterClass(): void
    {
        self::$wiremock?->stop();
        parent::tearDownAfterClass();
    }

    private static function registerStubs(): void
    {
        $adminUrl = "http://" . self::$host . ":" . self::$httpPort;

        // Stub 1 - OpenID Configuration
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

        // Stub 2 - Token endpoint
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
    }

    public function testCustomCaCert(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            caCertPath: self::$caCertPath,
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
    }

    public function testInsecureMode(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            insecure: true,
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
    }

    public function testDefaultHeaders(): void
    {
        $zitadel = Zitadel::withClientCredentials(
            "http://" . self::$host . ":" . self::$httpPort,
            "dummy-client",
            "dummy-secret",
            defaultHeaders: ["X-Custom-Header" => "test-value"],
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);

        // Verify via WireMock request journal
        $journal = json_decode(
            file_get_contents("http://" . self::$host . ":" . self::$httpPort . "/__admin/requests"),
            true
        );
        $foundHeader = false;
        foreach ($journal['requests'] as $req) {
            if (isset($req['request']['headers']['X-Custom-Header'])) {
                $foundHeader = true;
                break;
            }
        }
        $this->assertTrue($foundHeader, "Custom header should be present in WireMock request journal");
    }

    public function testProxyUrl(): void
    {
        $httpUrl = "http://" . self::$host . ":" . self::$httpPort;
        $zitadel = Zitadel::withClientCredentials(
            $httpUrl,
            "dummy-client",
            "dummy-secret",
            proxyUrl: "http://" . self::$host . ":" . self::$httpPort,
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
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

    public function testTransportOptionsObject(): void
    {
        $opts = new TransportOptions(insecure: true);
        $zitadel = Zitadel::withClientCredentials(
            "https://" . self::$host . ":" . self::$httpsPort,
            "dummy-client",
            "dummy-secret",
            transportOptions: $opts,
        );
        $this->assertInstanceOf(Zitadel::class, $zitadel);
    }
}
