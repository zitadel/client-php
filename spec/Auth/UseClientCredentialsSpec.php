<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Zitadel;

/**
 * SettingsService Integration Tests (Client Credentials)
 *
 * This suite verifies the Zitadel SettingsService API's general settings
 * endpoint works when authenticating via Client Credentials:
 *
 *  1. Retrieve general settings successfully with valid credentials
 *  2. Expect an ApiException when using invalid credentials
 */
class UseClientCredentialsSpec extends TestCase
{
    /**
     * Validate retrieval of general settings with valid client credentials.
     *
     * @throws ApiException on API error
     * @throws Exception
     * @doesNotPerformAssertions
     */
    public function testRetrievesGeneralSettingsWithValidAuth(): void
    {
        $client = Zitadel::withClientCredentials(
            self::env('BASE_URL'),
            self::env('CLIENT_ID'),
            self::env('CLIENT_SECRET')
        );

        $client->settings->settingsServiceGetGeneralSettings();
    }

    /**
     * Retrieve a configuration variable from the environment, falling back to $_ENV.
     */
    private static function env(string $key): string
    {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }

    /**
     * Expect an ApiException when using invalid client credentials.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withClientCredentials(
            self::env('BASE_URL'),
            'invalid',
            'invalid'
        );

        $this->expectException(ApiException::class);
        $invalid->settings->settingsServiceGetGeneralSettings();
    }
}
