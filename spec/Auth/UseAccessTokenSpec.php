<?php

namespace Zitadel\Client\Spec\Auth;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
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
class UseAccessTokenSpec extends TestCase
{
    /**
     * Retrieve a configuration variable from the environment, falling back to $_ENV.
     */
    private static function env(string $key): string
    {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }

    /**
     * Validate retrieval of general settings with a valid PAT.
     *
     * @throws ApiException on API error
     * @doesNotPerformAssertions
     */
    public function testRetrievesGeneralSettingsWithValidAuth(): void
    {
        $client = Zitadel::withAccessToken(
            self::env('BASE_URL'),
            self::env('AUTH_TOKEN')
        );

        $client->settings->settingsServiceGetGeneralSettings();
    }

    /**
     * Expect an ApiException when using an invalid PAT.
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withAccessToken(
            self::env('BASE_URL'),
            'invalid'
        );

        $this->expectException(ApiException::class);
        $invalid->settings->settingsServiceGetGeneralSettings();
    }
}
