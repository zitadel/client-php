<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use Zitadel\Client\ApiException;
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
        $client = Zitadel::withAccessToken(self::getBaseUrl(), self::getAuthToken());
        $client->settings->settingsServiceGetGeneralSettings();
    }

    /**
     * Expect an ApiException when using an invalid PAT.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withAccessToken(self::getBaseUrl(), 'invalid');

        $this->expectException(ZitadelException::class);
        $invalid->settings->settingsServiceGetGeneralSettings();
    }
}
