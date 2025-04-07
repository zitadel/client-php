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
   * @var Hostname
   */
  protected Hostname $hostName;

  /**
   * Authenticator constructor.
   *
   * @param Hostname $hostName The base URL for all authentication endpoints.
   */
  public function __construct(Hostname $hostName)
  {
    $this->hostName = $hostName;
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
   * @return Hostname The base URL for authentication endpoints.
   */
  public function getHost(): Hostname
  {
    return $this->hostName;
  }
}
