<?php

namespace Zitadel\Client;

use stdClass;

/**
 * Represents an HTTP error returned from the Zitadel API.
 *
 * Exposes the HTTP status code, response headers, and response body.
 */
class ApiException extends ZitadelException
{
    /**
     * The HTTP body of the server response (string, decoded JSON, or object).
     *
     * @var object|string|null
     */
    protected object|string|null $responseBody;

    /**
     * The HTTP headers of the server response.
     *
     * @var string[][]
     */
    protected array $responseHeaders;

    /**
     * Constructor.
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param string[][] $responseHeaders HTTP response headers
     * @param object|string|null $responseBody HTTP response body (string, decoded JSON, or object)
     */
    public function __construct(string $message, int $code, $responseHeaders = [], object|string|null $responseBody = null)
    {
        parent::__construct($message, $code);
        $this->responseHeaders = $responseHeaders;
        $this->responseBody = $responseBody;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return parent::getCode();
    }

    /**
     * Gets the HTTP response headers.
     *
     * @return string[][] HTTP response headers
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * Gets the HTTP response body (string, decoded JSON, or object).
     *
     * @return stdClass|string|null HTTP response body
     */
    public function getResponseBody(): string|stdClass|null
    {
        return $this->responseBody;
    }
}
