<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace Zitadel\Client\Auth;

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
   * @var string
   */
  protected string $tokenUrl;

  /**
   * The scope for the token request.
   *
   * @var string|null
   */
  protected ?string $scope;

  /**
   * The OAuth2 token (associative array containing at least 'access_token' and 'expires_at').
   *
   * @var AccessTokenInterface|null
   */
  protected ?AccessTokenInterface $token;

  /**
   * OAuthAuthenticator constructor.
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $tokenUrl The URL of the OAuth2 token endpoint.
   * @param string|null $scope The scope for the token request.
   */
  public function __construct(string $host, string $clientId, string $tokenUrl, ?string $scope = null)
  {
    parent::__construct($host);
    $this->clientId = $clientId;
    $this->tokenUrl = strpos($tokenUrl, '/') === 0 ? $host . $tokenUrl : $tokenUrl;
    $this->scope = $scope;
    $this->token = null;
  }

  /**
   * Retrieve the authentication token using the OAuth2 flow.
   *
   * This method checks if a valid token is available and refreshes it if necessary.
   *
   * @return string The authentication token
   */
  public function getAuthToken(): string
  {
    if ($this->token === null || $this->token->hasExpired()) {
      $this->refreshToken();
    }
    return $this->token->getToken();
  }

  /**
   * Refresh the access token.
   *
   * Subclasses must implement this method to refresh the token using their
   * specific OAuth flow.
   *
   * @return AccessTokenInterface
   */
  abstract public function refreshToken(): AccessTokenInterface;
}
