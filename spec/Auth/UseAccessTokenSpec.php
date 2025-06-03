<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zitadel\Client\ApiException;
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
class UseAccessTokenSpec extends TestCase
{
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

        $client->settings->getGeneralSettings(new stdClass());
    }

    /**
     * Retrieve a configuration variable from the environment, falling back to $_ENV.
     */
    private static function env(string $key): string
    {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }

    /**
     * Expect an ApiException when using an invalid PAT.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withAccessToken(
            self::env('BASE_URL'),
            'invalid'
        );

        $this->expectException(ZitadelException::class);
        $invalid->settings->getGeneralSettings(new stdClass());
    }
}
