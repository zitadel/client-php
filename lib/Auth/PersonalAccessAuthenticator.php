<?php

namespace Zitadel\Client\Auth;

/**
 * Personal Access Token Authenticator.
 *
 * Uses a static personal access token for API authentication.
 */
class PersonalAccessAuthenticator extends Authenticator
{
  /**
   * The personal access token.
   *
   * @var string
   */
  private string $token;

  /**
   * PersonalAccessAuthenticator constructor.
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $token The personal access token.
   */
  public function __construct(string $host, string $token)
  {
    parent::__construct(new Hostname($host));
    $this->token = $token;
  }

  /**
   * Retrieve authentication token using the personal access token.
   *
   * @return string The authentication token
   */
  public function getAuthToken(): string
  {
    return $this->token;
  }
}
