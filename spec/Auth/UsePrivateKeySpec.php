<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use Zitadel\Client\ApiException;
use Zitadel\Client\Spec\AbstractIntegrationTest;
use Zitadel\Client\Zitadel;
use Zitadel\Client\ZitadelException;

/**
 * SettingsService Integration Tests (Private Key Assertion)
 *
 * This suite verifies the Zitadel SettingsService API's general settings
 * endpoint works when authenticating via a private key assertion:
 *
 *  1. Retrieve general settings successfully with a valid private key
 *  2. Expect an ApiException when using an invalid private key
 */
class UsePrivateKeySpec extends AbstractIntegrationTest
{
    /**
     * Validate retrieval of general settings with a valid private key assertion.
     *
     * @throws ApiException on API error
     * @throws Exception
     * @doesNotPerformAssertions
     */
    public function testRetrievesGeneralSettingsWithValidAuth(): void
    {
        $client = Zitadel::withPrivateKey(self::getBaseUrl(), self::getJwtKey());
        $client->settings->getGeneralSettings();
    }

    /**
     * Expect an ApiException when using an invalid private key assertion.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withPrivateKey("https://zitadel.cloud", self::getJwtKey());

        $this->expectException(ZitadelException::class);
        $invalid->settings->getGeneralSettings();
    }
}
