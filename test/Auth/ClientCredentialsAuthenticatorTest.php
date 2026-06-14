<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use Exception;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;

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
}
