<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;
use LogicException;
use Zitadel\Client\ApiClient;
use Zitadel\Client\Errors\OAuth2ServerException;
use Zitadel\Client\Errors\OAuth2TokenException;

/**
 * Abstract base class for OAuth-based, token-minting authenticators.
 *
 * Mints a bearer token by POSTing an OAuth2 grant (client-credentials or a
 * signed JWT-bearer assertion) to the provider's token endpoint, then attaches
 * the resulting access token on every API request. The minted token is cached
 * together with its expiry and only re-minted once it is within the refresh
 * skew of expiring.
 *
 * Token-minting requires an outbound HTTP call, so this class implements
 * {@see HttpAwareAuthenticator}: the shared {@see ApiClient} is injected by the
 * {@see \Zitadel\Client\Zitadel} constructor and both OpenID discovery and the
 * token POST are sent through it. A token request fails with:
 *
 * - {@see LogicException} when no {@see ApiClient} has been injected;
 * - {@see \Zitadel\Client\Errors\NetworkException} or
 *   {@see \Zitadel\Client\Errors\NetworkTimeoutException} when no HTTP
 *   response arrived;
 * - {@see OAuth2ServerException} when the token endpoint answered with a
 *   non-2xx status;
 * - {@see OAuth2TokenException} when it answered 2xx without a usable access
 *   token.
 */
abstract class OAuthAuthenticator extends BaseAuthenticator implements HttpAwareAuthenticator
{
    /**
     * Seconds before expiry at which a cached token is treated as stale.
     */
    private const int REFRESH_SKEW_SECONDS = 300;

    private ?ApiClient $apiClient = null;

    private ?string $accessToken = null;

    private ?int $expiresAt = null;

    /**
     * @param OpenId $openId The OpenID discovery helper for the target host.
     * @param string $scope  Space-delimited scope string for the token request.
     */
    public function __construct(
        private readonly OpenId $openId,
        protected readonly string $scope
    ) {
    }

    #[\Override]
    public function setApiClient(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    #[\Override]
    public function getHost(): string
    {
        return $this->openId->getHostEndpoint();
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->getAuthToken()];
    }

    /**
     * Returns a valid access token, minting (or re-minting) one if the cache
     * is empty or within the refresh skew of expiring.
     */
    public function getAuthToken(): string
    {
        if ($this->accessToken === null || $this->isStale()) {
            return $this->refreshToken();
        }
        return $this->accessToken;
    }

    private function isStale(): bool
    {
        return $this->expiresAt !== null && time() >= $this->expiresAt - self::REFRESH_SKEW_SECONDS;
    }

    /**
     * Exchanges the configured grant for a fresh access token and caches it.
     *
     * @return string The freshly minted access token.
     */
    public function refreshToken(): string
    {
        $apiClient = $this->apiClient;
        if (!$apiClient instanceof ApiClient) {
            throw new LogicException(
                'OAuthAuthenticator has no ApiClient; use it through the Zitadel client, '
                . 'which injects one before the first token request.'
            );
        }

        $params = array_merge(
            ['grant_type' => $this->getGrantType(), 'scope' => $this->scope],
            $this->getTokenRequestParams()
        );

        $response = $apiClient->sendRequest(
            'POST',
            $this->openId->getTokenEndpoint($apiClient),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            http_build_query($params, '', '&', PHP_QUERY_RFC1738),
            /* never replay a token POST across a redirect: a malicious 307/308
             * could otherwise leak the assertion or secret. */
            true
        );

        $status = $response->statusCode;
        if ($status < 200 || $status >= 300) {
            throw $this->serverError($status, $response->body);
        }

        $payload = $this->parseObject($response->body);
        if ($payload === null) {
            throw new OAuth2TokenException('Token response is not a JSON object');
        }
        $accessToken = $payload['access_token'] ?? null;
        if (!is_string($accessToken) || $accessToken === '') {
            throw new OAuth2TokenException('Token response missing or empty access_token field');
        }
        $expiresIn = $payload['expires_in'] ?? null;
        $this->expiresAt = (is_int($expiresIn) || is_float($expiresIn)) && $expiresIn > 0
            ? time() + (int) $expiresIn
            : null;
        $this->accessToken = $accessToken;
        return $accessToken;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseObject(string $body): ?array
    {
        $payload = json_decode($body, true);
        return is_array($payload) && ($payload === [] || !array_is_list($payload)) ? $payload : null;
    }

    private function serverError(int $status, string $body): OAuth2ServerException
    {
        $payload = $this->parseObject($body);
        $code = $payload['error'] ?? null;
        if (!is_string($code) || $code === '') {
            return new OAuth2ServerException($status, null, null, null, $body);
        }
        $description = $payload['error_description'] ?? null;
        $uri = $payload['error_uri'] ?? null;
        return new OAuth2ServerException(
            $status,
            $code,
            is_string($description) ? $description : null,
            is_string($uri) ? $uri : null,
            $body
        );
    }

    /**
     * Returns `***` when a token is cached and null otherwise.
     */
    protected function maskedToken(): ?string
    {
        return $this->accessToken === null ? null : '***';
    }

    /**
     * Redacts the cached access token from var_dump() / print_r() output.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'host' => $this->getHost(),
            'scope' => $this->scope,
            'accessToken' => $this->maskedToken(),
        ];
    }

    /**
     * Throws {@see InvalidArgumentException} when the value is blank.
     *
     * @param string $value The value to check.
     * @param string $label The name used in the error message.
     */
    public static function requireText(string $value, string $label): string
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("$label cannot be empty.");
        }
        return $value;
    }

    /**
     * The OAuth2 grant_type value sent in the token request.
     */
    abstract protected function getGrantType(): string;

    /**
     * Grant-specific token-request parameters (e.g. assertion).
     *
     * @return array<string, string>
     */
    abstract protected function getTokenRequestParams(): array;
}
