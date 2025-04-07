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
  function __construct(string $host, string $jwtIssuer, string $jwtSubject, string $jwtAudience, string $privateKey)
  {
    parent::__construct($host);
    $this->jwtIssuer = $jwtIssuer;
    $this->jwtSubject = $jwtSubject;
    $this->jwtAudience = $jwtAudience;
    $this->privateKey = $privateKey;
    $this->jwtLifetime = new DateInterval('PT1H');
  }

  /**
   * Initialize a JWTAuthenticator instance from a JSON configuration file.
   *
   * The JSON file should have the following structure:
   * <code>
   * {
   *     "type": "serviceaccount",
   *     "keyId": "100509901696068329",
   *     "key": "-----BEGIN RSA PRIVATE KEY----- [...] -----END RSA PRIVATE KEY-----\n",
   *     "userId": "100507859606888466"
   * }
   * </code>
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $jsonPath The file path to the JSON configuration file.
   * @return JWTAuthenticatorBuilder An initialized instance of JWTAuthenticator.
   * @throws Exception if the file cannot be read or the JSON is invalid.
   */
  public static function fromJson(string $host, string $jsonPath): JWTAuthenticatorBuilder
  {
    $json = file_get_contents($jsonPath);
    if ($json === false) {
      throw new Exception("Unable to read JSON file: $jsonPath");
    }

    $config = json_decode($json, true);
    if ($config === null) {
      throw new Exception("Invalid JSON in file: $jsonPath");
    }

    $userId = $config['userId'] ?? null;
    $privateKey = $config['key'] ?? null;
    if ($userId === null || $privateKey === null) {
      throw new Exception("Missing required configuration keys in JSON file.");
    }

    return new JWTAuthenticatorBuilder($host, $userId, $userId, $host, $privateKey);
  }

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
