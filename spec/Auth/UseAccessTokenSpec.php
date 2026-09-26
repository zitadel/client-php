<?php

namespace Zitadel\Client\Spec\Auth;

use Zitadel\Client\Errors\UnauthorizedException;
use Exception;
use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\Auth\PersonalAccessTokenAuthenticator;
use Zitadel\Client\Spec\AbstractIntegrationTest;
use Zitadel\Client\Zitadel;

/**
 * SettingsService Integration Tests (Personal Access Token)
 *
 * This suite verifies the Zitadel SettingsService API's general settings
 * endpoint works when authenticating via Personal Access Token:
 *
 *  1. Retrieve general settings successfully with a valid token
 *  2. Expect an ApiException when using an invalid token
 */
class UseAccessTokenSpec extends AbstractIntegrationTest
{
    /**
     * Validate retrieval of general settings with a valid PAT.
     *
     * @throws ApiException on API error
     * @doesNotPerformAssertions
     */
    public function testRetrievesGeneralSettingsWithValidAuth(): void
    {
        $this->expectNotToPerformAssertions();
        $client = Zitadel::withAuthenticator(
            new PersonalAccessTokenAuthenticator(self::getBaseUrl(), self::getAuthToken()),
        );
        $client->settingsService->getGeneralSettings(new \stdClass());
    }

    /**
     * Expect an UnauthorizedException when using an invalid PAT.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withAuthenticator(
            new PersonalAccessTokenAuthenticator(self::getBaseUrl(), 'invalid'),
        );

        try {
            $invalid->settingsService->getGeneralSettings(new \stdClass());
            $this->fail('Expected UnauthorizedException');
        } catch (UnauthorizedException $e) {
            $this->assertSame(UnauthorizedException::class, $e::class);
        }
    }
}
