<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use Exception;
use Zitadel\Client\TransportOptions;

/**
 * OAuth2 Client Credentials Authenticator.
 *
 * Mints a bearer token via the OAuth2 client-credentials grant (RFC 6749 §4.4)
 * by POSTing the client_id / client_secret to the provider's token endpoint
 * through the SDK's shared transport. See {@see OAuthAuthenticator} for the
 * caching and HTTP-injection contract.
 */
class ClientCredentialsAuthenticator extends OAuthAuthenticator
{
    private const string GRANT_TYPE = 'client_credentials';

    /**
     * @param OpenId $hostName     Resolved OpenID configuration for the provider.
     * @param string $clientId     The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @param string $scope        Space-delimited scope string for the token request.
     */
    public function __construct(
        OpenId $hostName,
        string $clientId,
        private readonly string $clientSecret,
        string $scope = 'openid urn:zitadel:iam:org:project:id:zitadel:aud'
    ) {
        parent::__construct($hostName, $clientId, $scope);
    }

    /**
     * Returns a new builder instance for ClientCredentialsAuthenticator.
     *
     * @param string $host         The base URL for API endpoints.
     * @param string $clientId     The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @param TransportOptions|null $transportOptions Optional transport options
     *        for TLS, proxy, and headers (used while resolving OpenID discovery).
     * @return ClientCredentialsAuthenticatorBuilder A new builder instance.
     * @throws Exception
     */
    public static function builder(
        string $host,
        string $clientId,
        string $clientSecret,
        ?TransportOptions $transportOptions = null,
    ): ClientCredentialsAuthenticatorBuilder {
        return new ClientCredentialsAuthenticatorBuilder($host, $clientId, $clientSecret, $transportOptions);
    }

    protected function getGrantType(): string
    {
        return self::GRANT_TYPE;
    }

    /**
     * @return array<string, string>
     */
    protected function getAccessTokenOptions(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
        ];
    }
}
