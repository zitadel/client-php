<?php

namespace Zitadel\Client\Auth;

use DateInterval;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * JWT-based Authenticator using the JWT Bearer Grant (RFC7523).
 *
 * This class creates a JWT assertion and exchanges it for an access token.
 */
class JWTAuthenticator extends OAuthAuthenticator
{
  private const GRANT_TYPE = "urn:ietf:params:oauth:client-assertion-type:jwt-bearer";

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
    $this->jwtIssuer = $issuer;
    $this->jwtSubject = $subject;
    $this->jwtAudience = $audience;
    $this->privateKey = $privateKey;
    $this->jwtAlgorithm = $algorithm;
    $this->jwtLifetime = $jwtLifetime;
    parent::__construct($hostName, $clientId, $scope, new GenericProvider([
      'clientId' => $clientId,
      'urlAccessToken' => $authEndpoints->urlAccessToken->toString(),
      'urlAuthorize' => $authEndpoints->urlAuthorize->toString(),
      'urlResourceOwnerDetails' => $authEndpoints->urlResourceOwnerDetails->toString()
    ]));
    $this->provider->getGrantFactory()->setGrant(JWTAuthenticator::GRANT_TYPE, new JwtBearer());
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

  protected function getGrantType(): string
  {
    return JWTAuthenticator::GRANT_TYPE;
  }

  protected function getAccessTokenOptions(): array
  {
    $now = new DateTimeImmutable();
    $payload = [
      'iss' => $this->jwtIssuer,
      'sub' => $this->jwtSubject,
      'aud' => $this->jwtAudience,
      'iat' => $now->getTimestamp(),
      'exp' => $now->add($this->jwtLifetime)->getTimestamp(),
    ];
    return [
      'scope' => $this->scope,
      'assertion' => JWT::encode($payload, $this->privateKey, $this->jwtAlgorithm),
    ];
  }

}
