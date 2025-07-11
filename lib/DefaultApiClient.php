<?php

namespace Zitadel\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use InvalidArgumentException;
use RuntimeException;

/** @noinspection PhpUnused */

/**
 * A self-contained, Guzzle-based API client implementation.
 *
 *
 * This client supports custom Guzzle configuration via an optional callable,
 * allowing proxy settings, additional headers, middleware, etc.
 *
 * Example:
 * <code>
 * use GuzzleHttp\RequestOptions;
 * use Zitadel\Client\Configuration;
 * use Zitadel\Client\Auth\PersonalAccessAuthenticator;
 * use Zitadel\Client\DefaultApiClient;
 *
 * $config = new Configuration(new PersonalAccessAuthenticator('https://api.example.com', 'test-token'));
 *
 * $clientConfigurator = function (array $guzzleConfig): array {
 *     $guzzleConfig[RequestOptions::PROXY] = [
 *         'http'  => 'http://username:password@proxy.example.com:3128',
 *         'https' => 'http://username:password@proxy.example.com:3128',
 *     ];
 *
 *     $guzzleConfig[RequestOptions::HEADERS]['X-My-Custom-Header'] = 'custom-value';
 *     $guzzleConfig[RequestOptions::VERIFY] = false;
 *
 *     Return $guzzleConfig;
 * };
 *
 * // 3) Instantiate DefaultApiClient with the configurator
 * $apiClient = new DefaultApiClient($config, $clientConfigurator);
 * </code>
 *
 * @template T of object
 * @implements IApiClient<T>
 */
final class DefaultApiClient implements IApiClient
{
    private const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
    private readonly Client $client;

    public function __construct(
        private readonly Configuration $config,
        ?callable                      $clientConfigurator = null
    ) {
        $guzzleConfig = [
            RequestOptions::TIMEOUT => $this->config->getTimeout(),
            RequestOptions::CONNECT_TIMEOUT => $this->config->getConnectTimeout(),
        ];

        if ($clientConfigurator) {
            $guzzleConfig = $clientConfigurator($guzzleConfig);
        }

        $this->client = new Client($guzzleConfig);
    }

    public function invokeAPI(
        string  $operationId,
        string  $pathTemplate,
        string  $method,
        array   $pathParams,
        array   $queryParams,
        array   $headerParams,
        ?object $body,
        array   $responseTypes
    ): ?object {
        if (!in_array($method, self::VALID_METHODS, true)) {
            throw new InvalidArgumentException("Invalid HTTP method: $method");
        }

        $finalPath = $this->buildPath($pathTemplate, $pathParams);
        $uri = $this->config->getHost() . $finalPath;
        $httpBody = '';
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->config->getAccessToken(),
            'User-Agent' => $this->config->getUserAgent(),
            'X-Operation-Id' => $operationId,
        ];
        $headers = array_merge($defaultHeaders, $headerParams);

        if ($body !== null) {
            try {
                $headers['Content-Type'] = 'application/json';
                $httpBody = Utils::jsonEncode(
                    ObjectSerializer::sanitizeForSerialization($body),
                    JSON_THROW_ON_ERROR
                );
            } catch (Exception $e) {
                throw new RuntimeException("[$operationId] Failed to encode JSON body.", $e->getCode(), $e);
            }
        }

        $queryString = http_build_query($queryParams);
        if (!empty($queryString)) {
            $uri .= '?' . $queryString;
        }

        $request = new Request($method, $uri, $headers, $httpBody);

        try {
            $response = $this->client->send($request, [RequestOptions::HTTP_ERRORS => false]);
        } catch (GuzzleException $e) {
            throw new RuntimeException("[$operationId] API Request failed.", $e->getCode(), $e);
        }

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();
        $responseClass = $responseTypes[$statusCode] ?? null;

        if ($statusCode >= 200 && $statusCode < 300) {
            if ($responseClass) {
                try {
                    return ObjectSerializer::deserialize($responseBody, $responseClass);
                } catch (Exception $e) {
                    throw new RuntimeException("[$operationId] Failed to deserialize successful response.", $e->getCode(), $e);
                }
            }
            return null;
        }

        $errorBody = null;
        if ($responseClass) {
            try {
                $errorBody = ObjectSerializer::deserialize($responseBody, $responseClass);
            } catch (Exception) {
            }
        }
        if ($errorBody === null) {
            try {
                $errorBody = json_decode($responseBody, flags: JSON_THROW_ON_ERROR);
            } catch (Exception) {
                $errorBody = null;
            }
        }
        throw new ApiException("[$operationId] API Error", $statusCode, $response->getHeaders(), $errorBody);
    }

    /**
     * Builds a URL path by substituting placeholders with encoded values.
     *
     * @param string $pathTemplate The URL path with placeholders like /users/{id}.
     * @param array<string, scalar|null> $pathParams The parameters to substitute.
     * @return string The final, resolved URL path.
     */
    private function buildPath(string $pathTemplate, array $pathParams): string
    {
        $result = $pathTemplate;
        foreach ($pathParams as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result = str_replace('{' . $key . '}', urlencode((string)$value), $result);
        }
        return $result;
    }
}
