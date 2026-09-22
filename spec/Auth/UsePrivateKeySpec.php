<?php

namespace Zitadel\Client\Spec\Auth;

use Zitadel\Client\Errors\OAuth2ServerException;
use Exception;
use Zitadel\Client\ApiException;
use Zitadel\Client\Auth\WebTokenAuthenticator;
use Zitadel\Client\Spec\AbstractIntegrationTest;
use Zitadel\Client\Zitadel;

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
        $this->expectNotToPerformAssertions();
        $client = Zitadel::withAuthenticator(
            WebTokenAuthenticator::fromJson(self::getBaseUrl(), self::getJwtKey()),
        );
        $client->settingsService->getGeneralSettings(new \stdClass());
    }

    /**
     * Expect an OAuth2ServerException when signing with a key the instance does not know.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $pem);
        self::assertIsString($pem);
        $invalid = Zitadel::withAuthenticator(
            WebTokenAuthenticator::builder(self::getBaseUrl(), 'invalid', $pem)->keyId('invalid')->build(),
        );

        try {
            $invalid->settingsService->getGeneralSettings(new \stdClass());
            $this->fail('Expected OAuth2ServerException');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2ServerException::class, $e::class);
        }
    }
}
