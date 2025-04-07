<?php

namespace Zitadel\Client\Auth;

/**
 * Base builder for OAuth authenticators.
 *
 * Provides fluent methods to override the default token endpoint and scopes.
 * Subclasses extend this builder to construct specific OAuthAuthenticator instances.
 */
abstract class OAuthAuthenticatorBuilder
{
  protected string $host;
  protected string $tokenEndpoint = '/oauth/v2/token';
  protected string $authScopes = 'openid';

  /**
   * Constructs the builder with the required host.
   *
   * @param string $host The base URL for API endpoints.
   */
  public function __construct(string $host)
  {
    $this->host = $host;
  }

  /**
   * Overrides the default token endpoint.
   *
   * @param string $tokenEndpoint The URL (or relative path starting with '/') of the OAuth2 token endpoint.
   * @return self
   */
  public function tokenEndpoint(string $tokenEndpoint): self
  {
    $this->tokenEndpoint = $tokenEndpoint;
    return $this;
  }

  /**
   * Overrides the default scopes.
   *
   * @param array $authScopes A list of scopes for the token request.
   * @return self
   */
  public function scopes(array $authScopes): self
  {
    $this->authScopes = implode(' ', $authScopes);
    return $this;
  }
}
