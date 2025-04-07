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
  protected Hostname $hostName;
  protected AuthEndpoints $authEndpoints;
  protected string $authScopes = 'openid urn:zitadel:iam:org:project:id:zitadel:aud';

  /**
   * Constructs the builder with the required host.
   *
   * @param string $hostName
   */
  public function __construct(string $hostName)
  {
    $this->hostName = new Hostname($hostName);
    $this->authEndpoints = AuthEndpoints::getInstance($this->hostName);
  }

  /**
   * Overrides the default token endpoint.
   *
   * @param string $tokenEndpoint The URL (or relative path starting with '/') of the OAuth2 token endpoint.
   * @return self
   */
  public function tokenEndpoint(string $tokenEndpoint): self
  {
    $this->authEndpoints = new AuthEndpoints($this->hostName->getEndpointWithPath($tokenEndpoint), $this->authEndpoints->urlAuthorize, $this->authEndpoints->urlResourceOwnerDetails);
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
