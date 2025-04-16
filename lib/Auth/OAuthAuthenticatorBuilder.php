<?php

namespace Zitadel\Client\Auth;

use Exception;

/**
 * Base builder for OAuth authenticators.
 *
 * Provides fluent methods to override the default token endpoint and scopes.
 * Subclasses extend this builder to construct specific OAuthAuthenticator instances.
 */
abstract class OAuthAuthenticatorBuilder
{
  protected OpenId $hostName;
  protected string $authScopes = 'openid urn:zitadel:iam:org:project:id:zitadel:aud';

  /**
   * Constructs the builder with the required host.
   *
   * @param string $hostName
   * @throws Exception
   */
  public function __construct(string $hostName)
  {
    $this->hostName = new OpenId($hostName);
  }

  /**
   * Overrides the default scopes.
   *
   * @param string[] $authScopes A list of scopes for the token request.
   * @return static
   */
  public function scopes(array $authScopes): static
  {
    $this->authScopes = implode(' ', $authScopes);
    return $this;
  }
}
