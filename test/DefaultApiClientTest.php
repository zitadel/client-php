<?php

namespace Zitadel\Client\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;
use Zitadel\Client\ApiException;
use Zitadel\Client\Auth\NoAuthAuthenticator;
use Zitadel\Client\Configuration;
use Zitadel\Client\DefaultApiClient;
use Zitadel\Client\Model\ModelInterface;

class ErrorModel implements ModelInterface
{
    public function __construct(public ?string $errorCode = null, public ?string $field = null)
    {
    }

    public function getModelName(): string
    {
        return 'ErrorModel';
    }

    /**
     * @return string[] // Array of strings
     */
    public static function openAPITypes(): array
    {
        return [
            'errorCode' => 'string',
            'field' => 'string',
        ];
    }

    /**
     * @return string[] // Array of nullable strings
     */
    public static function openAPIFormats(): array
    {
        return [
            'errorCode' => 'string',
            'field' => 'string',
        ];
    }

    /**
     * @return string[] // Array of attribute mappings
     */
    public static function attributeMap(): array
    {
        return [
            'errorCode' => 'errorCode',
            'field' => 'field',
        ];
    }

    /**
     * @return string[] // Array of setter mappings
     */
    public static function setters(): array
    {
        return [
            'errorCode' => 'setErrorCode',
            'field' => 'setField',
        ];
    }

    /**
     * @return string[] // Array of getter mappings
     */
    public static function getters(): array
    {
        return [
            'errorCode' => 'getErrorCode',
            'field' => 'getField',
        ];
    }

    /**
     * @return string[] // List of invalid properties
     */
    public function listInvalidProperties(): array
    {
        return [];
    }

    public function valid(): bool
    {
        return true;
    }

    public static function isNullable(string $property): bool
    {
        return in_array($property, ['errorCode', 'field'], true);
    }

    public function isNullableSetToNull(string $property): bool
    {
        return $this->$property === null;
    }

    /** @noinspection PhpUnused */
    public function setErrorCode(?string $code): void
    {
        $this->errorCode = $code;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setField(?string $field): void
    {
        $this->field = $field;
    }

    public function getField(): ?string
    {
        return $this->field;
    }
}

/**
 * Success model to map the successful API response.
 */
class SuccessModel implements ModelInterface
{
    /**
     * SuccessModel constructor.
     *
     * @param string|null $status The status of the response.
     */
    public function __construct(public ?string $status = null)
    {
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName(): string
    {
        return 'SuccessModel';
    }

    /**
     * @return string[] // Array of strings
     */
    public static function openAPITypes(): array
    {
        return [
            'status' => 'string'
        ];
    }

    /**
     * @return string[] // Array of nullable strings
     */
    public static function openAPIFormats(): array
    {
        return [
            'status' => 'string'
        ];
    }

    /**
     * @return string[] // Array of attribute mappings
     */
    public static function attributeMap(): array
    {
        return [
            'status' => 'status'
        ];
    }

    /**
     * @return string[] // Array of setter mappings
     */
    public static function setters(): array
    {
        return [
            'status' => 'setStatus'
        ];
    }

    /**
     * @return string[] // Array of getter mappings
     */
    public static function getters(): array
    {
        return [
            'status' => 'getStatus'
        ];
    }

    /**
     * @return string[] // List of invalid properties
     */
    public function listInvalidProperties(): array
    {
        $invalidProperties = [];
        if ($this->status === null) {
            $invalidProperties[] = "status cannot be null";
        }
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model.
     * Return true if all passed.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return count($this->listInvalidProperties()) === 0;
    }

    /**
     * Checks if a property is nullable.
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        $nullableProperties = ['status'];  // example, can be extended if needed
        return in_array($property, $nullableProperties, true);
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        if ($this->$property === null) {
            return true;
        }
        return false;
    }

    /**
     * Get the value of the status property.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set the value of the status property.
     *
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }
}

class DefaultApiClientTest extends TestCase
{
    /**
     * @var string Holds the OAuth host URL constructed from the container's host
     * and mapped port.
     */
    protected static string $oauthHost;

    /**
     * @var StartedGenericContainer|null Holds the container instance.
     */
    private static ?StartedGenericContainer $mockOAuth2Server = null;

    /**
     * Set up the Docker container before any tests are run.
     *
     * Initializes a GenericContainer with the specified image, exposes port
     * 8080, and applies an HTTP wait strategy. Constructs the OAuth host URL
     * directly without intermediate variables.
     *
     * @return void
     * @throws GuzzleException
     * @throws GuzzleException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$mockOAuth2Server = (new GenericContainer("wiremock/wiremock:3.5.2"))
            ->withExposedPorts(8080)
            ->start();

        (new WaitForHttp(self::$mockOAuth2Server->getMappedPort(8080)))
            ->withPath("/__admin/mappings")
            ->withExpectedStatusCode(200)
            ->wait(self::$mockOAuth2Server);

        /** @noinspection HttpUrlsUsage */
        self::$oauthHost = "http://" . self::$mockOAuth2Server->getHost() . ":" . self::$mockOAuth2Server->getMappedPort(8080);

        $stubs = file_get_contents(__DIR__ . '/resources/api.json');
        $mappings = json_decode($stubs, true)['mappings'];

        $client = new Client();

        foreach ($mappings as $mapping) {
            $client->post(self::$oauthHost . '/__admin/mappings', [
                'json' => $mapping
            ]);
        }
    }

    /**
     * Tear down the Docker container after all tests are run.
     *
     * Stops the container if it was started.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$mockOAuth2Server?->stop();
        parent::tearDownAfterClass();
    }

    /**
     * Test GET request is successful.
     *
     * @return void
     * @throws ApiException
     * @throws ApiException
     */
    public function testGetRequest(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $response = $apiClient->invokeAPI(
            'testGetSuccess',
            '/users/123',
            'GET',
            [],
            [],
            [],
            null,
            SuccessModel::class,
            [
                200 => SuccessModel::class
            ]
        );

        $this->assertInstanceOf(SuccessModel::class, $response);
    }

    /**
     * Test POST request is successful.
     *
     * @return void
     * @throws ApiException
     * @throws ApiException
     */
    public function testPostRequest(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $responseTypes = [
            201 => SuccessModel::class
        ];

        $response = $apiClient->invokeAPI(
            'testPost',
            '/users',
            'POST',
            [],
            [],
            [],
            (object)['name' => 'John'],
            SuccessModel::class,
            $responseTypes
        );

        $this->assertInstanceOf(SuccessModel::class, $response);
    }

    /**
     * Test PUT request sends custom headers.
     *
     * @return void
     * @throws ApiException
     * @throws ApiException
     */
    public function testSendsCustomHeaders(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $apiClient->invokeAPI(
            'testCustomHeaders',
            '/users/123',
            'PUT',
            [],
            [],
            [
                'X-Request-ID' => 'test-uuid-123'
            ],
            new stdClass(),
            SuccessModel::class,
            [
                200 => SuccessModel::class
            ]
        );

        $this->expectNotToPerformAssertions();
    }

    /**
     * Test DELETE request returns void.
     *
     * @return void
     * @throws ApiException
     * @throws ApiException
     */
    public function testDeleteRequest(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $response = $apiClient->invokeAPI(
            'testVoid',
            '/users/123',
            'DELETE',
            [],
            [],
            [],
            null,
            SuccessModel::class,
            []
        );

        $this->assertNull($response);
    }

    /**
     * Test handling of 404 Not Found error.
     *
     * @return void
     */
    public function testApiClientErrorResponse(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $this->expectException(ApiException::class);

        $apiClient->invokeAPI(
            'test404',
            '/users/notfound',
            'GET',
            [],
            [],
            [],
            null,
            SuccessModel::class,
            []
        );
    }

    /**
     * Test handling of 400 Bad Request with a typed error model.
     *
     * @return void
     */
    public function testTypedClientErrorResponse(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $this->expectException(ApiException::class);

        $apiClient->invokeAPI(
            'test400',
            '/users/bad',
            'POST',
            [],
            [],
            [],
            new stdClass(),
            SuccessModel::class,
            [
                400 => ErrorModel::class
            ]
        );
    }

    /**
     * Test handling of malformed JSON response.
     *
     * @return void
     * @throws ApiException
     * @throws ApiException
     */
    public function testDeserializationFailure(): void
    {
        $config = new Configuration(new NoAuthAuthenticator(self::$oauthHost, "test-token"));
        $apiClient = new DefaultApiClient($config);

        $this->expectException(RuntimeException::class);

        $apiClient->invokeAPI(
            'testMalformed',
            '/malformed',
            'GET',
            [],
            [],
            [],
            null,
            SuccessModel::class,
            [
                200 => SuccessModel::class
            ]
        );
    }
}
