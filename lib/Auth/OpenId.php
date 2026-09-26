<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;
use JsonException;
use League\Uri\Uri;
use Zitadel\Client\ApiClient;
use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\Errors\SerializationException;

/**
 * Resolves the OpenID Connect discovery document for a Zitadel host.
 *
 * The constructor only validates and normalises the host; it performs no I/O.
 * The `token_endpoint` is fetched through the shared {@see ApiClient} the
 * first time {@see OpenId::getTokenEndpoint()} is called, so discovery
 * honours the SDK's proxy, TLS and timeout settings and fails with the same
 * error types as any other request:
 *
 * - no HTTP response: {@see \Zitadel\Client\Errors\NetworkException} or
 *   {@see \Zitadel\Client\Errors\NetworkTimeoutException};
 * - a non-2xx status: the {@see ApiException} subclass for that status;
 * - a body that is not a JSON object with a `token_endpoint`:
 *   {@see SerializationException}.
 */
class OpenId
{
    private const string WELL_KNOWN_PATH = '/.well-known/openid-configuration';

    private readonly string $hostEndpoint;

    private readonly string $wellKnownUrl;

    private ?string $tokenEndpoint = null;

    /**
     * Validates and normalises the host. A host without a scheme gets `https://`.
     *
     * @param string $host The Zitadel instance host name or URL.
     * @throws InvalidArgumentException If the host is empty, uses a scheme
     *                                  other than http or https, or is not a valid URL.
     */
    public function __construct(string $host)
    {
        $this->hostEndpoint = $this->normaliseHost($host);
        $this->wellKnownUrl = Uri::new($this->hostEndpoint)
            ->withPath(self::WELL_KNOWN_PATH)
            ->withQuery(null)
            ->withFragment(null)
            ->toString();
    }

    private function normaliseHost(string $host): string
    {
        $trimmed = trim($host);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Host cannot be empty.');
        }
        $lower = strtolower($trimmed);
        if (!str_starts_with($lower, 'http://') && !str_starts_with($lower, 'https://')) {
            if (str_contains($trimmed, '://')) {
                throw new InvalidArgumentException("Host must use the http or https scheme: $trimmed");
            }
            $trimmed = 'https://' . $trimmed;
        }
        $parts = parse_url($trimmed);
        if ($parts === false || !isset($parts['host']) || $parts['host'] === '') {
            throw new InvalidArgumentException("Host is not a valid URL: $trimmed");
        }
        return $trimmed;
    }

    /**
     * Returns the normalised host endpoint.
     */
    public function getHostEndpoint(): string
    {
        return $this->hostEndpoint;
    }

    /**
     * Returns the OAuth2 token endpoint, fetching the discovery document
     * through the given API client on first access and caching the result.
     *
     * @param ApiClient $apiClient The shared API client used for the discovery request.
     * @throws ApiException If discovery fails at the transport or HTTP level.
     * @throws SerializationException If the discovery document is unusable.
     */
    public function getTokenEndpoint(ApiClient $apiClient): string
    {
        return $this->tokenEndpoint ??= $this->discover($apiClient);
    }

    private function discover(ApiClient $apiClient): string
    {
        $url = $this->wellKnownUrl;
        $response = $apiClient->sendRequest('GET', $url, ['Accept' => 'application/json'], null);
        $status = $response->statusCode;
        if ($status < 200 || $status >= 300) {
            /* ApiException::fromResponse() owns the status-to-subclass table,
             * so a failed discovery raises exactly the typed error a regular
             * API call would for the same status. */
            throw ApiException::fromResponse($status, $response->headers, $response->body);
        }
        try {
            $document = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new SerializationException("OpenID configuration at $url is not a JSON object", 0, $e);
        }
        if (!is_array($document) || array_is_list($document)) {
            throw new SerializationException("OpenID configuration at $url is not a JSON object");
        }
        $endpoint = $document['token_endpoint'] ?? null;
        if (!is_string($endpoint) || $endpoint === '') {
            throw new SerializationException("OpenID configuration at $url has no valid token_endpoint");
        }
        return $endpoint;
    }
}
