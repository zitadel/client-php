<?php

namespace Zitadel\Client\Test\Auth;

use DateInterval;
use Exception;
use League\Uri\Uri;
use ReflectionClass;
use Zitadel\Client\Auth\OpenId;
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

    /**
     * The signing private key is masked in the default debug representation.
     * Rendering through print_r() invokes __debugInfo() so the raw key material
     * never leaks through var_dump() / stack traces / logs.
     */
    public function testRedactsSecret(): void
    {
        $privateKey = WebTokenAuthenticatorTest::getPrivateKey();

        $authenticator = new WebTokenAuthenticator(
            $this->fakeOpenId(),
            'visible-client-id',
            'openid',
            'issuer',
            'subject',
            'audience',
            $privateKey,
            new DateInterval('PT1H')
        );

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString($privateKey, $rendered);
        $this->assertStringContainsString('***', $rendered);
    }

    /**
     * Build an OpenId instance without performing the network discovery call its
     * constructor would otherwise make. The endpoints are not exercised by the
     * redaction assertions, so placeholder URIs are sufficient.
     */
    private function fakeOpenId(): OpenId
    {
        $openId = new ReflectionClass(OpenId::class)->newInstanceWithoutConstructor();

        foreach (['hostEndpoint', 'tokenEndpoint', 'authorizationEndpoint', 'userinfoEndpoint'] as $field) {
            $property = new ReflectionClass(OpenId::class)->getProperty($field);
            $property->setValue($openId, Uri::new('https://api.example.com'));
        }

        return $openId;
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
