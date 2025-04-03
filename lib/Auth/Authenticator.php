<?php

namespace Zitadel\Client\Auth;

/**
 * Base abstract class for all authentication strategies.
 *
 * This class defines a standard interface for retrieving authentication headers
 * for API requests.
 */
abstract class Authenticator
{
  /**
   * The base URL for authentication endpoints.
   *
   * @var string
   */
  protected string $host;

  /**
   * Authenticator constructor.
   *
   * @param string $host The base URL for all authentication endpoints.
   */
  public function __construct(string $host)
  {
    $this->host = $host;
  }

  /**
   * Retrieve the authentication token needed for API requests.
   *
   * @return string The authentication token
   */
  abstract public function getAuthToken(): string;

  /**
   * Retrieve the host URL.
   *
   * @return string The base URL for authentication endpoints.
   */
  public function getHost(): string
  {
    return $this->host;
  }
}
