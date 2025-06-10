<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zitadel\Client\ApiException;
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
class UsePrivateKeySpec extends TestCase
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
        $client = Zitadel::withPrivateKey(
            self::env('BASE_URL'),
            self::createTempJwtFile()
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

    private static function createTempJwtFile(): string
    {
        $k = self::env('JWT_KEY');
        $p = tempnam(sys_get_temp_dir(), 'jwt_') or exit;
        file_put_contents($p, $k) or exit;
        return $p;
    }

    /**
     * Expect an ApiException when using an invalid private key assertion.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withPrivateKey(
            "https://zitadel.cloud",
            self::createTempJwtFile()
        );

        $this->expectException(ZitadelException::class);
        $invalid->settings->getGeneralSettings(new stdClass());
    }
}
