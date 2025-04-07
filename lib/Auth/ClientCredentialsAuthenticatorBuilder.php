<?php

namespace Zitadel\Client\Auth;

/**
 * Builder for ClientCredentialsAuthenticator.
 *
 * Extends the base OAuthAuthenticatorBuilder to provide a fluent API for constructing
 * a ClientCredentialsAuthenticator instance.
 */
final class ClientCredentialsAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
  private string $clientId;
  private string $clientSecret;

  /**
   * Constructs the builder with the required parameters.
   *
   * @param string $host The base URL for API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $clientSecret The OAuth2 client secret.
   */
  public function __construct(string $host, string $clientId, string $clientSecret)
  {
    parent::__construct($host);
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
  }

  /**
   * Builds and returns a new ClientCredentialsAuthenticator instance.
   *
   * @return ClientCredentialsAuthenticator
   */
  public function build(): ClientCredentialsAuthenticator
  {
    return new ClientCredentialsAuthenticator(
      $this->host,
      $this->clientId,
      $this->clientSecret,
      $this->tokenEndpoint,
      $this->authScopes
    );
  }
}
