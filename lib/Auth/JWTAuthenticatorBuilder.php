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
final class JWTAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
  private string $jwtIssuer;
  private string $jwtSubject;
  private string $jwtAudience;
  private string $privateKey;
  private string $jwtAlgorithm = 'RS256';
  private DateInterval $jwtLifetime;

  /**
   * Constructs the builder with required parameters.
   *
   * @param string $host The base URL for API endpoints.
   * @param string $jwtIssuer The issuer claim for the JWT.
   * @param string $jwtSubject The subject claim for the JWT.
   * @param string $jwtAudience The audience claim for the JWT.
   * @param string $privateKey The PEM-formatted private key used to sign the JWT.
   */
  public function __construct(string $host, string $jwtIssuer, string $jwtSubject, string $jwtAudience, string $privateKey)
  {
    parent::__construct($host);
    $this->jwtIssuer = $jwtIssuer;
    $this->jwtSubject = $jwtSubject;
    $this->jwtAudience = $jwtAudience;
    $this->privateKey = $privateKey;
    $this->jwtLifetime = new DateInterval('PT1H');
  }

//  /**
//   * Creates a builder instance from a service account JSON file.
//   *
//   * The JSON file must contain keys "type", "keyId", "key", and "userId".
//   *
//   * @param string $host The base URL for API endpoints.
//   * @param string $jsonPath The path to the JSON configuration file.
//   * @return self
//   */
//  public static function fromKeyfile(string $host, string $jsonPath): self
//  {
//    $config = ServiceAccountConfig::fromPath($jsonPath);
//    return new self($host, $config->getUserId(), $config->getUserId(), $host, $config->getKeySpecAsPem());
//  }

  /**
   * Sets the token lifetime in seconds.
   *
   * @param int $seconds The lifetime of the JWT in seconds.
   * @return self
   * @throws Exception
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
   */
  public function jwtAlgorithm(string $jwtAlgorithm): self
  {
    $this->jwtAlgorithm = $jwtAlgorithm;
    return $this;
  }

  /**
   * Builds and returns a new JWTAuthenticator instance.
   *
   * Generates a JWT assertion using the provided parameters and then constructs
   * a JWTAuthenticator.
   *
   * @return JWTAuthenticator
   * @throws Exception if JWT generation fails.
   */
  public function build(): JWTAuthenticator
  {
    try {
      return new JWTAuthenticator(
        $this->hostName,
        "",
        $this->authScopes,
        $this->jwtIssuer,
        $this->jwtSubject,
        $this->jwtAudience,
        $this->privateKey,
        $this->authEndpoints,
        $this->jwtLifetime,
        $this->jwtAlgorithm,
      );
    } catch (Exception $e) {
      throw new Exception("Failed to generate JWT assertion: " . $e->getMessage(), 0, $e);
    }
  }
}
