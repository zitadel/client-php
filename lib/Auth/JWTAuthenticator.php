<?php

namespace Zitadel\Client\Auth;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * JWT-based Authenticator using the JWT Bearer Grant (RFC7523).
 *
 * This class creates a JWT assertion and exchanges it for an access token.
 */
class JWTAuthenticator extends OAuthAuthenticator
{
  private const GRANT_TYPE = "urn:ietf:params:oauth:client-assertion-type:jwt-bearer";

  private GenericProvider $provider;
  /**
   * The issuer claim for the JWT.
   *
   * @var string
   */
  private string $jwtIssuer;

  /**
   * The subject claim for the JWT.
   *
   * @var string
   */
  private string $jwtSubject;

  /**
   * The audience claim for the JWT.
   *
   * @var string
   */
  private string $jwtAudience;

  /**
   * The private key used to sign the JWT.
   *
   * @var string
   */
  private string $privateKey;

  /**
   * The signing algorithm.
   *
   * @var string
   */
  private string $jwtAlgorithm;

  /**
   * Lifetime of the JWT in seconds.
   *
   * @var DateInterval
   */
  private DateInterval $jwtLifetime;

  /**
   * JWTAuthenticator constructor.
   *
   * @param Hostname $hostName The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $scope
   * @param string $issuer The issuer claim for the JWT.
   * @param string $subject The subject claim for the JWT.
   * @param string $audience The audience claim.
   * @param string $privateKey The private key to sign the JWT.
   * @param AuthEndpoints $authEndpoints
   * @param DateInterval $jwtLifetime The lifetime of the JWT in seconds. Defaults to 300.
   * @param string $algorithm The signing algorithm. Defaults to "RS256".
   */
  function __construct(
    Hostname      $hostName,
    string        $clientId,
    string        $scope,
    string        $issuer,
    string        $subject,
    string        $audience,
    string        $privateKey,
    AuthEndpoints $authEndpoints,
    DateInterval  $jwtLifetime,
    string        $algorithm = 'RS256'
  )
  {
    parent::__construct($hostName, $clientId, $scope);
    $this->jwtIssuer = $issuer;
    $this->jwtSubject = $subject;
    $this->jwtAudience = $audience;
    $this->privateKey = $privateKey;
    $this->jwtAlgorithm = $algorithm;
    $this->jwtLifetime = $jwtLifetime;
    $this->provider = new GenericProvider([
      'clientId' => $this->clientId,
      'urlAccessToken' => $authEndpoints->urlAccessToken->toString(),
      'urlAuthorize' => $authEndpoints->urlAuthorize->toString(),
      'urlResourceOwnerDetails' => $authEndpoints->urlResourceOwnerDetails->toString()
    ]);
    $this->provider->getGrantFactory()->setGrant(JWTAuthenticator::GRANT_TYPE, new JwtBearer());
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
   * @param string $jsonPath The file path to the JSON configuration file.
   * @param string $host The base URL for the API endpoints.
   * @param string $tokenUrl The URL of the OAuth2 token endpoint.
   * @param string $audience The custom domain to be used as the 'aud' claim.
   * @param string $algorithm The signing algorithm. Defaults to "RS256".
   * @param int $tokenLifetime Lifetime of the JWT in seconds. Defaults to 300.
   * @return JWTAuthenticator An initialized instance of JWTAuthenticator.
   * @throws Exception if the file cannot be read or the JSON is invalid.
   */
  public static function fromJson(
    string $jsonPath,
    string $host,
    string $tokenUrl,
    string $audience,
    string $algorithm = 'RS256',
    int    $tokenLifetime = 300
  ): JWTAuthenticator
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
    return new self(
      $hostName,
      $userId,
      $tokenUrl,
      $userId,
      $userId,
      $audience,
      $privateKey,
      $algorithm,
      $tokenLifetime
    );
  }

  /**
   * Returns a new builder instance for ClientCredentialsAuthenticator.
   *
   * @param string $host The base URL for API endpoints.
   * @param string $userId
   * @param string $privateKey
   * @return JWTAuthenticatorBuilder A new builder instance.
   */
  public static function builder(string $host, string $userId, string $privateKey): JWTAuthenticatorBuilder
  {
    $hostName = new Hostname($host);
    return new JWTAuthenticatorBuilder($hostName->getEndpoint(), $userId, $userId, $hostName->getEndpoint(), $privateKey);
  }

  /**
   * Refresh the access token using a JWT assertion.
   *
   * This method generates a JWT assertion and exchanges it for an access token.
   *
   * @return AccessTokenInterface
   * @throws Exception|GuzzleException if the HTTP request fails or returns invalid JSON.
   */
  public function refreshToken(): AccessTokenInterface
  {
    $jwtAssertion = $this->generateJwtAssertion();

    try {
      $this->token = $this->provider->getAccessToken(JWTAuthenticator::GRANT_TYPE, [
        'scope' => $this->scope,
        'assertion' => $jwtAssertion,
      ]);

      if ($this->token === null) {
        throw new Exception('Unable to refresh token');
      } else {
        return $this->token;
      }
    } catch (IdentityProviderException $e) {
      throw new Exception('Token refresh failed: ' . $e->getMessage(), 0, $e);
    }
  }

  /**
   * Generate a JWT assertion based on the provided credentials and claims.
   *
   * @return string A signed JWT assertion.
   */
  private function generateJwtAssertion(): string
  {
    $now = new DateTimeImmutable();
    $payload = [
      'iss' => $this->jwtIssuer,
      'sub' => $this->jwtSubject,
      'aud' => $this->jwtAudience,
      'iat' => $now->getTimestamp(),
      'exp' => $now->add($this->jwtLifetime)->getTimestamp(),
    ];
    return JWT::encode($payload, $this->privateKey, $this->jwtAlgorithm);
  }
}
