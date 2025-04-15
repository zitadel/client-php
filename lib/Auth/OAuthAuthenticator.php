<?php

namespace Zitadel\Client\Auth;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Throwable;

/**
 * Abstract base class for OAuth-based authenticators.
 *
 * Provides common functionality for OAuth authenticators, including token management
 * and header construction.
 */
abstract class OAuthAuthenticator extends Authenticator
{

  /**
   * The OAuth2 token endpoint URL.
   *
   * @var OpenId
   */
  protected OpenId $openId;
  /**
   * The OAuth2 token (associative array containing at least 'access_token' and 'expires_at').
   *
   * @var AccessTokenInterface|null
   */
  protected ?AccessTokenInterface $token;

  /**
   * OAuthAuthenticator constructor.
   *
   * @param OpenId $openId
   * @param string $clientId The OAuth2 client identifier.
   * @param string $scope The scope for the token request.
   * @param GenericProvider $provider
   */
  public function __construct(OpenId           $openId, /**
   * The OAuth2 client identifier.
   */
                              protected string $clientId, /**
     * The scope for the token request.
     */
                              protected string $scope, protected GenericProvider $provider)
  {
    parent::__construct($openId->getHostEndpoint()->toString());
    $this->token = null;
    $this->openId = $openId;
  }

  /**
   * Retrieve the authentication token using the OAuth2 flow.
   *
   * This method checks if a valid token is available and refreshes it if necessary.
   *
   * @return string The authentication token
   * @throws Exception
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
   * @throws Exception if token fetch fails or response is invalid.
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
    } catch (Throwable $e) {
      throw new Exception('Token refresh failed: ' . $e->getMessage(), 0, $e);
    }
  }

  abstract protected function getGrantType(): string;

  /**
   * @return array<string, string>
   */
  abstract protected function getAccessTokenOptions(): array;
}
