<?php

namespace Zitadel\Client\Auth;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * OAuth2 Client Credentials Authenticator.
 *
 * Implements the OAuth2 client credentials grant to obtain an access token.
 */
class ClientCredentialsAuthenticator extends OAuthAuthenticator
{
    private const GRANT_TYPE = "client_credentials";

    /**
     * Constructs a ClientCredentialsAuthenticator.
     *
     * @param OpenId $hostName The base URL for the API endpoints.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @param string $scope The scope for the token request.
     */
    public function __construct(
        OpenId $hostName,
        string $clientId,
        string $clientSecret,
        string $scope = 'openid urn:zitadel:iam:org:project:id:zitadel:aud'
    ) {
        parent::__construct($hostName, $clientId, $scope, new GenericProvider([
          'clientId' => $clientId,
          'clientSecret' => $clientSecret,
          'urlAccessToken' => $hostName->getTokenEndpoint()->toString(),
          'urlAuthorize' => $hostName->getAuthorizationEndpoint()->toString(),
          'urlResourceOwnerDetails' => $hostName->getUserinfoEndpoint()->toString(),
        ]));
    }

    /**
     * Returns a new builder instance for ClientCredentialsAuthenticator.
     *
     * @param string $host The base URL for API endpoints.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @return ClientCredentialsAuthenticatorBuilder A new builder instance.
     * @throws Exception
     */
    public static function builder(string $host, string $clientId, string $clientSecret): ClientCredentialsAuthenticatorBuilder
    {
        return new ClientCredentialsAuthenticatorBuilder($host, $clientId, $clientSecret);
    }

    protected function getGrantType(): string
    {
        return ClientCredentialsAuthenticator::GRANT_TYPE;
    }

    protected function getAccessTokenOptions(): array
    {
        return [
          'scope' => $this->scope,
        ];
    }
}
