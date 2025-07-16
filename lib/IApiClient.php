<?php

namespace Zitadel\Client;

/**
 * @template T of object
 */
interface IApiClient
{
    /**
     * @param string $operationId
     * @param string $pathTemplate
     * @param string $method
     * @param array<string, scalar|null> $pathParams
     * @param array<string, mixed> $queryParams
     * @param array<string, string|string[]> $headerParams
     * @param object|null $body
     * @param array<int|string, class-string>|null $errorTypes A map of status codes (e.g., 404, "4XX", "default") to error response types.
     * @return void
     * @throws ZitadelException
     */
    public function invokeAPINoResponse(
        string  $operationId,
        string  $pathTemplate,
        string  $method,
        array   $pathParams,
        array   $queryParams,
        array   $headerParams,
        ?object $body,
        ?array  $errorTypes = null
    ): void;

    /**
     * @param string $operationId
     * @param string $pathTemplate
     * @param string $method
     * @param array<string, scalar|null> $pathParams
     * @param array<string, mixed> $queryParams
     * @param array<string, string|string[]> $headerParams
     * @param object|null $body
     * @param class-string<T>|null $successType The expected response type for a successful (2xx) response.
     * @param array<int|string, class-string>|null $errorTypes A map of status codes (e.g., 404, "4XX", "default") to error response types.
     * @return object
     * @throws ZitadelException
     */
    public function invokeAPI(
        string  $operationId,
        string  $pathTemplate,
        string  $method,
        array   $pathParams,
        array   $queryParams,
        array   $headerParams,
        ?object $body,
        ?string $successType = null,
        ?array  $errorTypes = null
    ): object;
}
