<?php

namespace Zitadel\Client\Test\Auth;

use Exception;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;

/**
 * Tests for the ClientCredentialsAuthenticator.
 *
 * This test verifies that the client credentials authenticator correctly refreshes its token
 * and returns the proper Authorization header.
 */
class ClientCredentialsAuthenticatorTest extends OAuthAuthenticatorTest
{
    /**
     * @throws Exception
     */
    public function testRefreshToken(): void
    {
        sleep(20);

        $authenticator = ClientCredentialsAuthenticator::builder(static::$oauthHost, "dummy-client", "dummy-secret")
            ->scopes(["openid", "foo"])
            ->build();

        $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
        $token = $authenticator->refreshToken();
        $this->assertNotEmpty($token->getToken(), "Access token should not be empty");
        $this->assertFalse($token->hasExpired(), "Token expiry should be in the future");
        $this->assertEquals($token->getToken(), $authenticator->getAuthToken());
        $this->assertEquals($authenticator->getHost()->toString(), static::$oauthHost);
        $this->assertNotEquals($authenticator->refreshToken()->getToken(), $authenticator->refreshToken()->getToken());
    }
}
