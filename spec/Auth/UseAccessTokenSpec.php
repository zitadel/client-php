<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use Zitadel\Client\ApiException;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Spec\AbstractIntegrationTest;
use Zitadel\Client\Zitadel;
use Zitadel\Client\ZitadelException;

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
            new PersonalAccessAuthenticator(self::getBaseUrl(), self::getAuthToken()),
        );
        $client->settingsService->getGeneralSettings(new \stdClass());
    }

    /**
     * Expect an ApiException when using an invalid PAT.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withAuthenticator(
            new PersonalAccessAuthenticator(self::getBaseUrl(), 'invalid'),
        );

        $this->expectException(ZitadelException::class);
        $invalid->settingsService->getGeneralSettings(new \stdClass());
    }
}
