<?php

namespace Zitadel\Client\Test\Auth;

use DateInterval;
use Exception;
use InvalidArgumentException;
use Zitadel\Client\Auth\OpenId;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class WebTokenAuthenticatorTest extends OAuthAuthenticatorTestCase
{
    /**
     * @var list<string>
     */
    private array $keyFiles = [];

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
        $key = openssl_pkey_get_private($privateKey);
        self::assertNotFalse($key);
        $authenticator = new WebTokenAuthenticator(
            new OpenId('https://api.example.com'),
            'openid',
            'issuer',
            'subject',
            'audience',
            $key,
            new DateInterval('PT1H')
        );

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString($privateKey, $rendered);
        $this->assertStringContainsString('host', $rendered);
    }

    /**
     * A Zitadel key file builds an authenticator for the host.
     */
    public function testLoadsKeyFile(): void
    {
        $path = $this->keyFile((string) json_encode([
            'type' => 'serviceaccount',
            'keyId' => 'key-1',
            'userId' => 'user-1',
            'key' => WebTokenAuthenticatorTest::getPrivateKey(),
        ]));

        $authenticator = WebTokenAuthenticator::fromJson('https://example.zitadel.cloud', $path);

        $this->assertSame('https://example.zitadel.cloud', $authenticator->getHost());
    }

    /**
     * A missing or malformed key file is an InvalidArgumentException.
     */
    public function testRejectsBadKeyFile(): void
    {
        $host = 'https://example.zitadel.cloud';
        $paths = [sys_get_temp_dir() . '/absent-zitadel-key.json'];
        foreach (
            [
                'not json',
                '[]',
                '{"userId":"user-1","keyId":"key-1"}',
                '{"userId":"user-1","keyId":"key-1","key":"not a pem"}',
            ] as $content
        ) {
            $paths[] = $this->keyFile($content);
        }

        foreach ($paths as $path) {
            try {
                WebTokenAuthenticator::fromJson($host, $path);
                $this->fail("Expected InvalidArgumentException for $path");
            } catch (InvalidArgumentException $e) {
                $this->assertSame(InvalidArgumentException::class, $e::class);
            }
        }
    }

    /**
     * Invalid builder arguments are an InvalidArgumentException.
     */
    public function testRejectsBadBuilderArguments(): void
    {
        $host = 'https://example.zitadel.cloud';
        $pem = WebTokenAuthenticatorTest::getPrivateKey();
        $builder = WebTokenAuthenticator::builder($host, 'user-1', $pem);

        foreach (
            [
                fn (): \Zitadel\Client\Auth\WebTokenAuthenticatorBuilder => WebTokenAuthenticator::builder($host, '', $pem),
                fn (): \Zitadel\Client\Auth\WebTokenAuthenticatorBuilder => WebTokenAuthenticator::builder($host, 'user-1', 'not a pem'),
                fn (): \Zitadel\Client\Auth\WebTokenAuthenticatorBuilder => $builder->jwtAlgorithm('HS256'),
                fn (): \Zitadel\Client\Auth\WebTokenAuthenticatorBuilder => $builder->tokenLifetimeSeconds(0),
                fn (): \Zitadel\Client\Auth\WebTokenAuthenticatorBuilder => $builder->keyId(''),
            ] as $action
        ) {
            try {
                $action();
                $this->fail('Expected InvalidArgumentException');
            } catch (InvalidArgumentException $e) {
                $this->assertSame(InvalidArgumentException::class, $e::class);
            }
        }
    }

    private function keyFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'zitadel-key');
        self::assertNotFalse($path);
        file_put_contents($path, $content);
        $this->keyFiles[] = $path;
        return $path;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->keyFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
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
