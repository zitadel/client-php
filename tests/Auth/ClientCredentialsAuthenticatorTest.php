<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use Exception;
use League\Uri\Uri;
use ReflectionClass;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\OpenId;

/**
 * Tests for the ClientCredentialsAuthenticator.
 *
 * This test verifies that the client credentials authenticator correctly refreshes its token
 * and returns the proper Authorization header.
 */
class ClientCredentialsAuthenticatorTest extends OAuthAuthenticatorTestCase
{
    /**
     * @throws Exception
     */
    public function testRefreshToken(): void
    {
        sleep(20);

        $authenticator = $this->withApiClient(
            ClientCredentialsAuthenticator::builder(static::$oauthHost, "dummy-client", "dummy-secret")
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
     * The client secret is masked in the default debug representation while the
     * client id stays visible. Rendering through print_r() invokes __debugInfo()
     * so the raw secret never leaks through var_dump() / stack traces / logs.
     */
    public function testRedactsSecret(): void
    {
        $authenticator = new ClientCredentialsAuthenticator(
            $this->fakeOpenId(),
            'visible-client-id',
            'super-secret-value'
        );

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString('super-secret-value', $rendered);
        $this->assertStringContainsString('***', $rendered);
        $this->assertStringContainsString('visible-client-id', $rendered);
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
}
