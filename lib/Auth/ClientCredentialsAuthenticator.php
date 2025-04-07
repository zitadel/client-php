<?php

namespace Zitadel\Client\Auth;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * OAuth2 Client Credentials Authenticator.
 *
 * Implements the OAuth2 client credentials grant to obtain an access token.
 */
class ClientCredentialsAuthenticator extends OAuthAuthenticator
{
  private const GRANT_TYPE = "client_credentials";
  private GenericProvider $provider;

  /**
   * Constructs a ClientCredentialsAuthenticator.
   *
   * @param Hostname $hostName The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $clientSecret The OAuth2 client secret.
   * @param AuthEndpoints $authEndpoints
   * @param string $scope The scope for the token request.
   */
  function __construct(
    Hostname      $hostName,
    string        $clientId,
    string        $clientSecret,
    AuthEndpoints $authEndpoints,
    string        $scope = 'openid urn:zitadel:iam:org:project:id:zitadel:aud'
  )
  {
    parent::__construct($hostName, $clientId, $scope);
    $this->provider = new GenericProvider([
      'clientId' => $this->clientId,
      'clientSecret' => $clientSecret,
      'urlAccessToken' => $authEndpoints->urlAccessToken->toString(),
      'urlAuthorize' => $authEndpoints->urlAuthorize->toString(),
      'urlResourceOwnerDetails' => $authEndpoints->urlResourceOwnerDetails->toString()
    ]);
  }

  /**
   * Returns a new builder instance for ClientCredentialsAuthenticator.
   *
   * @param string $host The base URL for API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $clientSecret The OAuth2 client secret.
   * @return ClientCredentialsAuthenticatorBuilder A new builder instance.
   */
  public static function builder(string $host, string $clientId, string $clientSecret): ClientCredentialsAuthenticatorBuilder
  {
    return new ClientCredentialsAuthenticatorBuilder($host, $clientId, $clientSecret);
  }

  /**
   * Refreshes the access token using the client credentials grant.
   *
   * Uses the league/oauth2-client library to obtain an access token.
   *
   * @return AccessTokenInterface
   * @throws Exception|GuzzleException if the token request fails.
   */
  public function refreshToken(): AccessTokenInterface
  {
    try {
      $this->token = $this->provider->getAccessToken(ClientCredentialsAuthenticator::GRANT_TYPE, [
        'scope' => $this->scope,
      ]);

      if ($this->token === null) {
        throw new Exception('Unable to refresh token');
      } else {
        return $this->token;
      }
    } catch (IdentityProviderException $e) {
      throw new Exception('Token refresh failed: ' . $e->getMessage(), 0, $e);
    }
  }
}
