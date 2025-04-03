<?php

namespace Zitadel\Client\Auth;

use Exception;
use Firebase\JWT\JWT;

/**
 * JWT-based Authenticator using the JWT Bearer Grant (RFC7523).
 *
 * This class creates a JWT assertion and exchanges it for an access token.
 */
class JWTAuthenticator extends OAuthAuthenticator
{
  /**
   * The issuer claim for the JWT.
   *
   * @var string
   */
  private string $issuer;

  /**
   * The subject claim for the JWT.
   *
   * @var string
   */
  private string $subject;

  /**
   * The audience claim for the JWT.
   *
   * @var string
   */
  private string $audience;

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
  private string $algorithm;

  /**
   * Lifetime of the JWT in seconds.
   *
   * @var int
   */
  private int $tokenLifetime;

  /**
   * JWTAuthenticator constructor.
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $issuer The issuer claim for the JWT.
   * @param string $subject The subject claim for the JWT.
   * @param string $audience The audience claim.
   * @param string $privateKey The private key to sign the JWT.
   * @param string|null $tokenUrl The URL of the OAuth2 token endpoint.
   * @param string $algorithm The signing algorithm. Defaults to "RS256".
   * @param int $tokenLifetime The lifetime of the JWT in seconds. Defaults to 300.
   */
  public function __construct(
    string  $host,
    string  $clientId,
    string  $issuer,
    string  $subject,
    string  $audience,
    string  $privateKey,
    ?string $tokenUrl,
    string  $algorithm = 'RS256',
    int     $tokenLifetime = 300
  )
  {
    parent::__construct($host, $clientId, $tokenUrl);
    $this->issuer = $issuer;
    $this->subject = $subject;
    $this->audience = $audience;
    $this->privateKey = $privateKey;
    $this->algorithm = $algorithm;
    $this->tokenLifetime = $tokenLifetime;
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
      $host,
      $userId,
      $tokenUrl,
      $userId,      // issuer
      $userId,      // subject
      $audience,
      $privateKey,
      $algorithm,
      $tokenLifetime
    );
  }

  /**
   * Refresh the access token using a JWT assertion.
   *
   * This method generates a JWT assertion and exchanges it for an access token.
   *
   * @return void
   * @throws Exception if the HTTP request fails or returns invalid JSON.
   */
  public function refreshToken(): void
  {
    $jwtAssertion = $this->generateJwtAssertion();
    $postData = http_build_query([
      'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
      'assertion' => $jwtAssertion,
      'client_id' => $this->clientId
    ]);
    $this->token = $this->sendPostRequest($this->tokenUrl, $postData);
  }

  /**
   * Generate a JWT assertion based on the provided credentials and claims.
   *
   * @return string A signed JWT assertion.
   */
  private function generateJwtAssertion(): string
  {
    $currentTime = time();
    $payload = [
      'iss' => $this->issuer,
      'sub' => $this->subject,
      'aud' => $this->audience,
      'iat' => $currentTime,
      'exp' => $currentTime + $this->tokenLifetime,
      'jti' => (string)$currentTime
    ];
    return JWT::encode($payload, $this->privateKey, $this->algorithm);
  }

  /**
   * Sends a POST request to the given URL with the provided data.
   *
   * @param string $url The endpoint URL.
   * @param string $postData The URL-encoded POST data.
   * @return array The JSON-decoded response as an associative array.
   * @throws Exception if the request fails or returns invalid JSON.
   */
  private function sendPostRequest(string $url, string $postData): array
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/x-www-form-urlencoded'
    ]);
    $result = curl_exec($ch);
    if ($result === false) {
      throw new Exception('CURL error: ' . curl_error($ch));
    }
    curl_close($ch);
    $decoded = json_decode($result, true);
    if ($decoded === null) {
      throw new Exception('Invalid JSON response.');
    }
    return $decoded;
  }
}
