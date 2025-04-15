<?php

namespace Zitadel\Client\Auth;

use DateInterval;
use Exception;

/**
 * Builder for JWTAuthenticator.
 *
 * Provides a fluent API for configuring and constructing a JWTAuthenticator.
 * This builder extends the base OAuthAuthenticatorBuilder.
 *
 * Usage:
 * <pre>
 *   $authenticator = JWTAuthenticator::builder("https://api.example.com", "issuer", "subject", "audience", $privateKey)
 *       ->tokenEndpoint("/oauth/v2/token")
 *       ->scopes(["openid", "foo"])
 *       ->tokenLifetimeSeconds(3600)
 *       ->jwtAlgorithm("RS256")
 *       ->build();
 * </pre>
 *
 * A convenience method "fromKeyfile" is provided to create the builder using a service account JSON file.
 */
final class WebTokenAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
  private string $jwtAlgorithm = 'RS256';
  private DateInterval $jwtLifetime;
  private ?string $keyId = null;

  /**
   * Constructs the builder with required parameters.
   *
   * @param string $host The base URL for API endpoints.
   * @param string $jwtIssuer The issuer claim for the JWT.
   * @param string $jwtSubject The subject claim for the JWT.
   * @param string $jwtAudience The audience claim for the JWT.
   * @param string $privateKey The PEM-formatted private key used to sign the JWT.
   * @throws Exception
   */
  function __construct(string $host, private string $jwtIssuer, private string $jwtSubject, private string $jwtAudience, private string $privateKey)
  {
    parent::__construct($host);
    $this->jwtLifetime = new DateInterval('PT1H');
  }

  /**
   * Sets the token lifetime in seconds.
   *
   * @param int $seconds The lifetime of the JWT in seconds.
   * @return self
   * @throws Exception
   * @noinspection PhpUnused
   */
  public function tokenLifetimeSeconds(int $seconds): self
  {
    $this->jwtLifetime = new DateInterval('PT' . $seconds . 'S');
    return $this;
  }

  /**
   * Sets the JWT signing algorithm.
   *
   * @param string $jwtAlgorithm The JWT signing algorithm (e.g., "RS256").
   * @return self
   * @noinspection PhpUnused
   */
  public function jwtAlgorithm(string $jwtAlgorithm): self
  {
    $this->jwtAlgorithm = $jwtAlgorithm;
    return $this;
  }

  public function keyId(string $keyId): self
  {
    $this->keyId = $keyId;
    return $this;
  }


  /**
   * Builds and returns a new JWTAuthenticator instance.
   *
   * Generates a JWT assertion using the provided parameters and then constructs
   * a JWTAuthenticator.
   *
   * @return WebTokenAuthenticator
   * @throws Exception if JWT generation fails.
   */
  public function build(): WebTokenAuthenticator
  {
    return new WebTokenAuthenticator(
      $this->hostName,
      "zitadel",
      $this->authScopes,
      $this->jwtIssuer,
      $this->jwtSubject,
      $this->jwtAudience,
      $this->privateKey,
      $this->jwtLifetime,
      jwtAlgorithm: $this->jwtAlgorithm,
      keyId: $this->keyId,
    );
  }
}
