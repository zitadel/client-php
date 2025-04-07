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
  private GenericProvider $provider;

  /**
   * Constructs a ClientCredentialsAuthenticator.
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $clientSecret The OAuth2 client secret.
   * @param string|null $tokenUrl The URL of the OAuth2 token endpoint.
   *                                  If relative, it will be prepended with the host.
   * @param string $scope The scope for the token request.
   */
  public function __construct(
    string  $host,
    string  $clientId,
    string  $clientSecret,
    ?string $tokenUrl,
    string  $scope = 'openid urn:zitadel:iam:org:project:id:myprojectid:aud additional_scope'
  )
  {
    $fullTokenUrl = (strpos($tokenUrl, '/') === 0) ? $host . $tokenUrl : $tokenUrl;
    parent::__construct($host, $clientId, $fullTokenUrl, $scope);
    $this->provider = new GenericProvider([
      'clientId' => $this->clientId,
      'clientSecret' => $clientSecret,
      'urlAccessToken' => $fullTokenUrl,
      'urlAuthorize' => 'https://service.example.com/authorize', #FIXME
      'urlResourceOwnerDetails' => 'https://service.example.com/resource'
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
      $this->token = $this->provider->getAccessToken('client_credentials', [
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
