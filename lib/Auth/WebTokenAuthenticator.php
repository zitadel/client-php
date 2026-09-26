<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use DateInterval;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use LogicException;
use OpenSSLAsymmetricKey;
use Throwable;

/**
 * JWT-bearer authenticator using the JWT Bearer Grant (RFC 7523).
 *
 * Signs a short-lived JWT assertion with firebase/php-jwt and exchanges it at
 * the provider's token endpoint for an access token. The exchange is sent
 * through the SDK's shared transport; see {@see OAuthAuthenticator} for the
 * caching and HTTP-injection contract.
 */
class WebTokenAuthenticator extends OAuthAuthenticator
{
    private const string GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    /**
     * @param OpenId               $openId       The OpenID discovery helper for the target host.
     * @param string               $scope        Space-delimited scope string for the token request.
     * @param string               $jwtIssuer    The JWT issuer (iss) claim.
     * @param string               $jwtSubject   The JWT subject (sub) claim.
     * @param string               $jwtAudience  The JWT audience (aud) claim.
     * @param OpenSSLAsymmetricKey $privateKey   The RSA private key used to sign the JWT.
     * @param DateInterval         $jwtLifetime  Lifetime of the JWT assertion.
     * @param string               $jwtAlgorithm The JWT signing algorithm.
     * @param string|null          $keyId        Optional key id (kid) header.
     */
    public function __construct(
        OpenId $openId,
        string $scope,
        private readonly string $jwtIssuer,
        private readonly string $jwtSubject,
        private readonly string $jwtAudience,
        private readonly OpenSSLAsymmetricKey $privateKey,
        private readonly DateInterval $jwtLifetime,
        private readonly string $jwtAlgorithm = 'RS256',
        private readonly ?string $keyId = null,
    ) {
        parent::__construct($openId, $scope);
    }

    /**
     * Creates a WebTokenAuthenticator from a Zitadel service-account key file.
     *
     * Expected JSON format:
     *
     * ```json
     * {
     *   "type": "serviceaccount",
     *   "keyId": "<key-id>",
     *   "key": "<private-key>",
     *   "userId": "<user-id>"
     * }
     * ```
     *
     * @param string $host     Base URL for the API endpoints.
     * @param string $jsonPath File path to the key file.
     * @throws InvalidArgumentException If the file cannot be read, is not a JSON
     *                                  object, lacks the string fields userId,
     *                                  keyId and key, or holds an invalid key.
     */
    public static function fromJson(string $host, string $jsonPath): WebTokenAuthenticator
    {
        $json = is_file($jsonPath) && is_readable($jsonPath) ? file_get_contents($jsonPath) : false;
        if ($json === false) {
            throw new InvalidArgumentException("Unable to read the key file at $jsonPath");
        }
        $config = json_decode($json, true);
        if (!is_array($config) || ($config !== [] && array_is_list($config))) {
            throw new InvalidArgumentException("The key file at $jsonPath is not a JSON object");
        }
        $userId = $config['userId'] ?? null;
        $keyId = $config['keyId'] ?? null;
        $privateKey = $config['key'] ?? null;
        if (!is_string($userId) || !is_string($keyId) || !is_string($privateKey)) {
            throw new InvalidArgumentException(
                "The key file at $jsonPath must contain the string fields userId, keyId and key"
            );
        }
        return self::builder($host, $userId, $privateKey)->keyId($keyId)->build();
    }

    /**
     * Returns a builder for a WebTokenAuthenticator.
     *
     * @param string $host       The base URL for the OAuth provider.
     * @param string $userId     The user ID, used as both the issuer and the subject.
     * @param string $privateKey The PEM-encoded RSA private key used to sign the JWT.
     * @throws InvalidArgumentException If the host is not a valid http or https
     *                                  URL, the user ID is empty, or the key is
     *                                  not an RSA private key.
     */
    public static function builder(string $host, string $userId, string $privateKey): WebTokenAuthenticatorBuilder
    {
        return new WebTokenAuthenticatorBuilder($host, $userId, $privateKey);
    }

    #[\Override]
    protected function getGrantType(): string
    {
        return self::GRANT_TYPE;
    }

    /**
     * Builds a freshly signed JWT assertion with time-sensitive claims.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function getTokenRequestParams(): array
    {
        $now = new DateTimeImmutable();
        $payload = [
            'iss' => $this->jwtIssuer,
            'sub' => $this->jwtSubject,
            'aud' => $this->jwtAudience,
            'iat' => $now->getTimestamp(),
            'exp' => $now->add($this->jwtLifetime)->getTimestamp(),
        ];
        try {
            $assertion = JWT::encode($payload, $this->privateKey, $this->jwtAlgorithm, $this->keyId);
        } catch (Throwable $e) {
            throw new LogicException('Unable to sign the JWT assertion', 0, $e);
        }
        return ['assertion' => $assertion];
    }
}
