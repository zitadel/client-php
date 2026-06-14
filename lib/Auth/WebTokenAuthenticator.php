<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Zitadel\Client\TransportOptions;

/**
 * JWT-bearer Authenticator using the JWT Bearer Grant (RFC 7523).
 *
 * Signs a short-lived JWT assertion with firebase/php-jwt and exchanges it at
 * the provider's token endpoint for an access token. The exchange is sent
 * through the SDK's shared transport; see {@see OAuthAuthenticator} for the
 * caching and HTTP-injection contract.
 */
class WebTokenAuthenticator extends OAuthAuthenticator
{
    /** Wire grant_type for the RFC 7523 JWT-bearer flow. */
    private const string GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    /**
     * @param OpenId       $hostName    Resolved OpenID configuration for the provider.
     * @param string       $clientId    The OAuth2 client identifier.
     * @param string       $scope       Space-delimited scope string for the token request.
     * @param string       $jwtIssuer   The issuer (iss) claim for the JWT.
     * @param string       $jwtSubject  The subject (sub) claim for the JWT.
     * @param string       $jwtAudience The audience (aud) claim for the JWT.
     * @param string       $privateKey  The PEM private key used to sign the JWT.
     * @param DateInterval $jwtLifetime The lifetime of the JWT assertion.
     * @param string       $jwtAlgorithm The signing algorithm. Defaults to "RS256".
     * @param string|null  $keyId       Optional key id (kid) header.
     */
    public function __construct(
        OpenId $hostName,
        string $clientId,
        string $scope,
        private readonly string $jwtIssuer,
        private readonly string $jwtSubject,
        private readonly string $jwtAudience,
        private readonly string $privateKey,
        private readonly DateInterval $jwtLifetime,
        private readonly string $jwtAlgorithm = 'RS256',
        private readonly ?string $keyId = null,
    ) {
        parent::__construct($hostName, $clientId, $scope);
    }

    /**
     * Initialize a WebTokenAuthenticator from a service-account JSON file.
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
     * @param string $host     The base URL for the API endpoints.
     * @param string $jsonPath The file path to the JSON configuration file.
     * @param TransportOptions|null $transportOptions Optional transport options.
     * @return WebTokenAuthenticator An initialized instance.
     * @throws Exception if the file cannot be read or the JSON is invalid.
     * @noinspection SpellCheckingInspection
     */
    public static function fromJson(
        string $host,
        string $jsonPath,
        ?TransportOptions $transportOptions = null,
    ): WebTokenAuthenticator {
        $json = file_get_contents($jsonPath);
        if ($json === false) {
            throw new Exception("Unable to read JSON file: $jsonPath");
        }

        $config = json_decode($json, true);
        if (!is_array($config)) {
            throw new Exception("Invalid JSON in file: $jsonPath");
        }

        $userId = $config['userId'] ?? null;
        $privateKey = $config['key'] ?? null;
        $keyId = $config['keyId'] ?? null;
        if (!is_string($userId) || !is_string($privateKey) || !is_string($keyId)) {
            throw new Exception("Missing or invalid required configuration keys in JSON file.");
        }

        return self::builder($host, $userId, $privateKey, $transportOptions)
            ->keyId($keyId)
            ->build();
    }

    /**
     * Returns a new builder instance for WebTokenAuthenticator.
     *
     * @param string $host       The base URL for API endpoints.
     * @param string $userId     The user id used as issuer/subject.
     * @param string $privateKey The PEM private key used to sign the JWT.
     * @param TransportOptions|null $transportOptions Optional transport options.
     * @return WebTokenAuthenticatorBuilder A new builder instance.
     * @throws Exception
     */
    public static function builder(
        string $host,
        string $userId,
        string $privateKey,
        ?TransportOptions $transportOptions = null,
    ): WebTokenAuthenticatorBuilder {
        return new WebTokenAuthenticatorBuilder($host, $userId, $userId, $host, $privateKey, $transportOptions);
    }

    protected function getGrantType(): string
    {
        return self::GRANT_TYPE;
    }

    /**
     * @return array<string, string>
     */
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
