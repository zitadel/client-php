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
     * @param array<int|string, class-string<T>> $responseTypes
     * @return object|null
     */
    public function invokeAPI(
        string  $operationId,
        string  $pathTemplate,
        string  $method,
        array   $pathParams,
        array   $queryParams,
        array   $headerParams,
        ?object $body,
        array   $responseTypes
    ): ?object;
}
