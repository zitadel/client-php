<?php

namespace Zitadel\Client\Test\Auth;

use Exception;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class WebTokenAuthenticatorTest extends OAuthAuthenticatorTestCase
{
    /**
     * @throws Exception
     */
    public function testRefreshToken(): void
    {
        $authenticator = $this->withApiClient(
            WebTokenAuthenticator::builder(static::$oauthHost, "1", WebTokenAuthenticatorTest::getPrivateKey())
                ->build()
        );

        $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
        $token = $authenticator->refreshToken();
        $this->assertNotEmpty($token, "Access token should not be empty");
        $this->assertEquals($token, $authenticator->getAuthToken());
        $this->assertEquals($authenticator->getHost(), static::$oauthHost);
        $this->assertNotEquals($authenticator->refreshToken(), $authenticator->refreshToken());
    }

    private static function getPrivateKey(): string
    {
        $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $res = openssl_pkey_new($config);
        self::assertNotFalse($res);
        openssl_pkey_export($res, $key);
        return $key;
    }

    /**
     * @throws Exception
     */
    public function testRefreshTokenWithRS256(): void
    {
        $authenticator = $this->withApiClient(
            WebTokenAuthenticator::builder(static::$oauthHost, "1", WebTokenAuthenticatorTest::getPrivateKey())
                ->jwtAlgorithm("RS256")
                ->build()
        );

        $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
        $token = $authenticator->refreshToken();
        $this->assertNotEmpty($token, "Access token should not be empty");
        $this->assertEquals($token, $authenticator->getAuthToken());
        $this->assertEquals($authenticator->getHost(), static::$oauthHost);
        $this->assertNotEquals($authenticator->refreshToken(), $authenticator->refreshToken());
    }

    /**
     * @throws Exception
     */
    public function testRefreshTokenWithExtendedLifetime(): void
    {
        $authenticator = $this->withApiClient(
            WebTokenAuthenticator::builder(static::$oauthHost, "1", WebTokenAuthenticatorTest::getPrivateKey())
                ->tokenLifetimeSeconds(86400)
                ->build()
        );

        $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
        $token = $authenticator->refreshToken();
        $this->assertNotEmpty($token, "Access token should not be empty");
        $this->assertEquals($token, $authenticator->getAuthToken());
        $this->assertEquals($authenticator->getHost(), static::$oauthHost);
        $this->assertNotEquals($authenticator->refreshToken(), $authenticator->refreshToken());
    }
}
