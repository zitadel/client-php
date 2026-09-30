<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;

/**
 * OAuth authenticator implementing the client-credentials flow (RFC 6749 §4.4).
 *
 * Mints a bearer token by POSTing client_id / client_secret to the provider's
 * token endpoint through the SDK's shared transport. See
 * {@see OAuthAuthenticator} for the caching and HTTP-injection contract.
 */
class ClientCredentialsAuthenticator extends OAuthAuthenticator
{
    private const string GRANT_TYPE = 'client_credentials';

    /**
     * @param OpenId $openId       The OpenID discovery helper for the target host.
     * @param string $clientId     The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @param string $scope        Space-delimited scope string for the token request.
     */
    public function __construct(
        OpenId $openId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        string $scope = OAuthAuthenticatorBuilder::DEFAULT_SCOPE
    ) {
        parent::__construct($openId, $scope);
    }

    /**
     * Returns a builder for a ClientCredentialsAuthenticator.
     *
     * @param string $host         The base URL for the OAuth provider.
     * @param string $clientId     The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @throws InvalidArgumentException If the host is not a valid http or https
     *                                  URL, or the client identifier or secret is empty.
     */
    public static function builder(
        string $host,
        string $clientId,
        string $clientSecret,
    ): ClientCredentialsAuthenticatorBuilder {
        return new ClientCredentialsAuthenticatorBuilder($host, $clientId, $clientSecret);
    }

    #[\Override]
    protected function getGrantType(): string
    {
        return self::GRANT_TYPE;
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function getTokenRequestParams(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }

    /**
     * Redacts the client secret and cached access token from var_dump() /
     * print_r() output while keeping the client id visible.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function __debugInfo(): array
    {
        return [
            'host' => $this->getHost(),
            'clientId' => $this->clientId,
            'clientSecret' => '***',
            'scope' => $this->scope,
            'accessToken' => $this->maskedToken(),
        ];
    }
}
