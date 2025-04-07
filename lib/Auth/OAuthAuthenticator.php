<?php

namespace Zitadel\Client\Auth;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Abstract base class for OAuth-based authenticators.
 *
 * Provides common functionality for OAuth authenticators, including token management
 * and header construction.
 */
abstract class OAuthAuthenticator extends Authenticator
{

  /**
   * The OAuth2 client identifier.
   *
   * @var string
   */
  protected string $clientId;
  /**
   * The OAuth2 token endpoint URL.
   *
   * @var Hostname
   */
  protected Hostname $hostName;
  /**
   * The scope for the token request.
   *
   * @var string
   */
  protected string $scope;
  /**
   * The OAuth2 token (associative array containing at least 'access_token' and 'expires_at').
   *
   * @var AccessTokenInterface|null
   */
  protected ?AccessTokenInterface $token;
  protected GenericProvider $provider;

  /**
   * OAuthAuthenticator constructor.
   *
   * @param Hostname $hostName The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string|null $scope The scope for the token request.
   */
  public function __construct(Hostname $hostName, string $clientId, string $scope, GenericProvider $provider)
  {
    parent::__construct($hostName);
    $this->clientId = $clientId;
    $this->scope = $scope;
    $this->token = null;
    $this->provider = $provider;
  }

  /**
   * Retrieve the authentication token using the OAuth2 flow.
   *
   * This method checks if a valid token is available and refreshes it if necessary.
   *
   * @return string The authentication token
   * @throws Exception
   * @throws GuzzleException
   */
  public function getAuthToken(): string
  {
    if ($this->token === null || $this->token->hasExpired()) {
      $this->refreshToken();
    }
    return $this->token->getToken();
  }

  /**
   * Refresh the access token using the configured grant type and options.
   *
   * @return AccessTokenInterface
   * @throws Exception|GuzzleException if token fetch fails or response is invalid.
   */
  public function refreshToken(): AccessTokenInterface
  {
    try {
      $this->token = $this->provider->getAccessToken(
        $this->getGrantType(),
        $this->getAccessTokenOptions()
      );

      if ($this->token === null) {
        throw new Exception('Unable to refresh token');
      }

      return $this->token;
    } catch (IdentityProviderException $e) {
      throw new Exception('Token refresh failed: ' . $e->getMessage(), 0, $e);
    }
  }

  abstract protected function getGrantType(): string;

  /**
   * @return array<string, string>
   */
  abstract protected function getAccessTokenOptions(): array;
}
