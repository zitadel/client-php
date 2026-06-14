<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use Zitadel\Client\ApiClient;
use Zitadel\Client\ApiException;

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
 * {@see HttpAwareAuthenticator}: the {@see ApiClient} is injected by the
 * {@see \Zitadel\Client\Client} constructor and the token POST is sent through
 * it. Sharing the SDK transport means token exchange honours the same proxy,
 * TLS, timeout and redirect configuration as regular API calls.
 *
 * (Previously this exchange went through league/oauth2-client's GenericProvider;
 * that dependency has been dropped in favour of the injected SDK transport.
 * firebase/php-jwt is retained for signing the JWT-bearer assertion.)
 */
abstract class OAuthAuthenticator extends BaseAuthenticator implements HttpAwareAuthenticator
{
    /**
     * Seconds before expiry at which a cached token is treated as stale and
     * re-minted. Mirrors the previous 5-minute skew.
     */
    private const int REFRESH_SKEW_SECONDS = 300;

    /** Resolved OpenID configuration (host + token endpoint). */
    protected OpenId $openId;

    /** The injected shared API client used for the token exchange. */
    protected ?ApiClient $apiClient = null;

    /** The currently cached access token, or null if none has been minted. */
    private ?string $accessToken = null;

    /** Unix timestamp at which the cached token expires (0 if unknown). */
    private int $expiresAt = 0;

    /**
     * @param OpenId $openId   Resolved OpenID configuration for the provider.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $scope    Space-delimited scope string for the token request.
     */
    public function __construct(
        OpenId $openId,
        protected string $clientId,
        protected string $scope
    ) {
        $this->openId = $openId;
    }

    public function setApiClient(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function getHost(): string
    {
        return $this->openId->getHostEndpoint()->toString();
    }

    /**
     * @return array<string, string>
     */
    public function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->getAuthToken()];
    }

    /**
     * Return a valid access token, minting (or re-minting) one if the cache is
     * empty or within the refresh skew of expiring.
     *
     * @throws ApiException if the token cannot be obtained.
     */
    public function getAuthToken(): string
    {
        if (
            $this->accessToken === null
            || ($this->expiresAt !== 0 && time() >= ($this->expiresAt - self::REFRESH_SKEW_SECONDS))
        ) {
            $this->refreshToken();
        }

        /** @var string $token guaranteed non-null after refreshToken() */
        $token = $this->accessToken;
        return $token;
    }

    /**
     * Exchange the configured grant for a fresh access token and cache it.
     *
     * POSTs an `application/x-www-form-urlencoded` body to the token endpoint
     * through the injected {@see ApiClient}. Subclasses contribute the
     * grant_type and the grant-specific parameters (scope, assertion, ...).
     *
     * @return string the freshly minted access token.
     * @throws ApiException if the client is not yet injected or the exchange fails.
     */
    public function refreshToken(): string
    {
        if (!$this->apiClient instanceof ApiClient) {
            throw new ApiException(
                'OAuthAuthenticator has no ApiClient; it must be used via the '
                . 'Zitadel\\Client\\Client, which injects the shared transport '
                . 'before any token exchange.'
            );
        }

        $params = array_merge(
            ['grant_type' => $this->getGrantType()],
            $this->getAccessTokenOptions()
        );

        $response = $this->apiClient->sendRequest(
            'POST',
            $this->openId->getTokenEndpoint()->toString(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            http_build_query($params, '', '&', PHP_QUERY_RFC1738),
            /* noRedirect: never replay a token POST across a redirect — a
             * malicious 307/308 could otherwise leak the assertion/secret. */
            true
        );

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new ApiException(
                'Token refresh failed: token endpoint returned HTTP ' . $response->statusCode,
                $response->statusCode,
                $response->headers,
                $response->body
            );
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($response->body, true);
        if (!is_array($payload) || !isset($payload['access_token']) || !is_string($payload['access_token'])) {
            throw new ApiException(
                'Token refresh failed: token endpoint response did not contain an access_token.',
                $response->statusCode,
                $response->headers,
                $response->body
            );
        }

        $this->accessToken = $payload['access_token'];
        $expiresIn = isset($payload['expires_in']) && is_numeric($payload['expires_in'])
            ? (int) $payload['expires_in']
            : 0;
        $this->expiresAt = $expiresIn > 0 ? time() + $expiresIn : 0;

        return $this->accessToken;
    }

    /**
     * Masks any cached token so it never leaks through var_dump() / print_r()
     * / stack traces / error logs.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'host' => $this->getHost(),
            'clientId' => $this->clientId,
            'scope' => $this->scope,
            'accessToken' => $this->accessToken === null ? null : '***',
            'expiresAt' => $this->expiresAt,
        ];
    }

    /**
     * The OAuth2 grant_type value sent in the token request.
     */
    abstract protected function getGrantType(): string;

    /**
     * Grant-specific token-request parameters (e.g. scope, assertion).
     *
     * @return array<string, string>
     */
    abstract protected function getAccessTokenOptions(): array;
}
