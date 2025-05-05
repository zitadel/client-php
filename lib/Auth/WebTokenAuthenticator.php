<?php

namespace Zitadel\Client\Auth;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * JWT-based Authenticator using the JWT Bearer Grant (RFC7523).
 *
 * This class creates a JWT assertion and exchanges it for an access token.
 */
class WebTokenAuthenticator extends OAuthAuthenticator
{
    private const GRANT_TYPE = "urn:ietf:params:oauth:client-assertion-type:jwt-bearer";

    /**
     * JWTAuthenticator constructor.
     *
     * @param OpenId $hostName The base URL for the API endpoints.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $scope
     * @param string $jwtIssuer The issuer claim for the JWT.
     * @param string $jwtSubject The subject claim for the JWT.
     * @param string $jwtAudience The audience claim.
     * @param string $privateKey The private key to sign the JWT.
     * @param DateInterval $jwtLifetime The lifetime of the JWT in seconds. Defaults to 300.
     * @param string $jwtAlgorithm The signing algorithm. Defaults to "RS256".
     * @param string|null $keyId
     */
    public function __construct(
        OpenId               $hostName,
        string               $clientId,
        string               $scope,
        /**
         * The issuer claim for the JWT.
         */
        private string       $jwtIssuer,
        /**
         * The subject claim for the JWT.
         */
        private string       $jwtSubject,
        /**
         * The audience claim for the JWT.
         */
        private string       $jwtAudience,
        /**
         * The private key used to sign the JWT.
         */
        private string       $privateKey,
        /**
         * Lifetime of the JWT in seconds.
         */
        private DateInterval $jwtLifetime,
        /**
         * The signing algorithm.
         */
        private string       $jwtAlgorithm = 'RS256',
        private ?string      $keyId = null
    ) {
        parent::__construct($hostName, $clientId, $scope, new GenericProvider([
            'clientId' => $clientId,
            'urlAccessToken' => $hostName->getTokenEndpoint()->toString(),
            'urlAuthorize' => $hostName->getAuthorizationEndpoint()->toString(),
            'urlResourceOwnerDetails' => $hostName->getUserinfoEndpoint()->toString(),
        ]));
        $this->provider->getGrantFactory()->setGrant(WebTokenAuthenticator::GRANT_TYPE, new JwtBearer());
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
     * @return WebTokenAuthenticator An initialized instance of JWTAuthenticator.
     * @throws Exception if the file cannot be read or the JSON is invalid.
     * @noinspection SpellCheckingInspection
     */
    public static function fromJson(string $host, string $jsonPath): WebTokenAuthenticator
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
        $keyId = $config['keyId'] ?? null;
        if ($userId === null || $privateKey === null || $keyId === null) {
            throw new Exception("Missing required configuration keys in JSON file.");
        }

        return self::builder($host, $userId, $privateKey)->keyId($keyId)->build();
    }

    /**
     * Returns a new builder instance for ClientCredentialsAuthenticator.
     *
     * @param string $host The base URL for API endpoints.
     * @param string $userId
     * @param string $privateKey
     * @return WebTokenAuthenticatorBuilder A new builder instance.
     * @throws Exception
     */
    public static function builder(string $host, string $userId, string $privateKey): WebTokenAuthenticatorBuilder
    {
        return new WebTokenAuthenticatorBuilder($host, $userId, $userId, $host, $privateKey);
    }

    protected function getGrantType(): string
    {
        return WebTokenAuthenticator::GRANT_TYPE;
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
            'assertion' => JWT::encode($payload, $this->privateKey, $this->jwtAlgorithm, $this->keyId),
        ];
    }

}
