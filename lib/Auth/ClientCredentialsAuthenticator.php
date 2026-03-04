<?php

namespace Zitadel\Client\Auth;

use Exception;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Zitadel\Client\TransportOptions;

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
     * @param TransportOptions|null $transportOptions Optional transport options for HTTP connections.
     */
    public function __construct(
        OpenId $hostName,
        string $clientId,
        string $clientSecret,
        string $scope = 'openid urn:zitadel:iam:org:project:id:zitadel:aud',
        ?TransportOptions $transportOptions = null
    ) {
        $transportOptions ??= TransportOptions::defaults();

        $guzzleOpts = $transportOptions->toGuzzleOptions();
        $collaborators = !empty($guzzleOpts) ? ['httpClient' => new Client($guzzleOpts)] : [];

        parent::__construct($hostName, $clientId, $scope, new GenericProvider([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'urlAccessToken' => $hostName->getTokenEndpoint()->toString(),
            'urlAuthorize' => $hostName->getAuthorizationEndpoint()->toString(),
            'urlResourceOwnerDetails' => $hostName->getUserinfoEndpoint()->toString(),
        ], $collaborators), $transportOptions);
    }

    /**
     * Returns a new builder instance for ClientCredentialsAuthenticator.
     *
     * @param string $host The base URL for API endpoints.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @param TransportOptions|null $transportOptions Optional transport options for HTTP connections.
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
        return ClientCredentialsAuthenticator::GRANT_TYPE;
    }

    protected function getAccessTokenOptions(): array
    {
        return [
            'scope' => $this->scope,
        ];
    }
}
