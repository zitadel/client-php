<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use DateInterval;
use InvalidArgumentException;
use OpenSSLAsymmetricKey;

/**
 * Builder for {@see WebTokenAuthenticator}.
 */
final class WebTokenAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
    private const array ALGORITHMS = ['RS256', 'RS384', 'RS512'];

    private readonly string $userId;

    private readonly OpenSSLAsymmetricKey $privateKey;

    private DateInterval $jwtLifetime;

    private string $jwtAlgorithm = 'RS256';

    private ?string $keyId = null;

    /**
     * @param string $host       The base URL for the OAuth provider.
     * @param string $userId     The user ID, used as both the issuer and the subject.
     * @param string $privateKey The PEM-encoded RSA private key used to sign the JWT.
     * @throws InvalidArgumentException If the host is not a valid http or https
     *                                  URL, the user ID is empty, or the key is
     *                                  not an RSA private key.
     */
    public function __construct(string $host, string $userId, string $privateKey)
    {
        parent::__construct($host);
        $this->userId = OAuthAuthenticator::requireText($userId, 'User ID');
        $this->privateKey = $this->loadPrivateKey($privateKey);
        $this->jwtLifetime = new DateInterval('PT1H');
    }

    private function loadPrivateKey(string $pem): OpenSSLAsymmetricKey
    {
        $key = openssl_pkey_get_private($pem);
        $details = $key === false ? false : openssl_pkey_get_details($key);
        if ($key === false || $details === false || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new InvalidArgumentException('Private key is not a valid RSA private key.');
        }
        return $key;
    }

    /**
     * Sets the JWT assertion lifetime in seconds.
     *
     * @param int $seconds Lifetime of the JWT in seconds; must be positive.
     * @throws InvalidArgumentException If the lifetime is not positive.
     */
    public function tokenLifetimeSeconds(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException('Token lifetime must be a positive number of seconds.');
        }
        $this->jwtLifetime = new DateInterval('PT' . $seconds . 'S');
        return $this;
    }

    /**
     * Sets the JWT signing algorithm.
     *
     * @param string $jwtAlgorithm One of RS256, RS384 or RS512.
     * @throws InvalidArgumentException If the algorithm is not supported.
     */
    public function jwtAlgorithm(string $jwtAlgorithm): self
    {
        if (!in_array($jwtAlgorithm, self::ALGORITHMS, true)) {
            throw new InvalidArgumentException(
                "Unsupported JWT algorithm '$jwtAlgorithm'; use RS256, RS384 or RS512."
            );
        }
        $this->jwtAlgorithm = $jwtAlgorithm;
        return $this;
    }

    /**
     * Sets the key ID sent as the kid header of the assertion.
     *
     * @param string $keyId The key identifier.
     * @throws InvalidArgumentException If the key ID is empty.
     */
    public function keyId(string $keyId): self
    {
        $this->keyId = OAuthAuthenticator::requireText($keyId, 'Key ID');
        return $this;
    }

    /**
     * Builds the WebTokenAuthenticator.
     */
    public function build(): WebTokenAuthenticator
    {
        return new WebTokenAuthenticator(
            $this->openId,
            $this->scope,
            $this->userId,
            $this->userId,
            $this->openId->getHostEndpoint(),
            $this->privateKey,
            $this->jwtLifetime,
            $this->jwtAlgorithm,
            $this->keyId,
        );
    }
}
