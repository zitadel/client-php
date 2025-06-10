<?php

namespace Zitadel\Client\Spec\Auth;

use Exception;
use Zitadel\Client\ApiException;
use Zitadel\Client\Spec\AbstractIntegrationTest;
use Zitadel\Client\Zitadel;
use Zitadel\Client\ZitadelException;

/**
 * SettingsService Integration Tests (Client Credentials)
 *
 * This suite verifies the Zitadel SettingsService API's general settings
 * endpoint works when authenticating via Client Credentials:
 *
 *  1. Retrieve general settings successfully with valid credentials
 *  2. Expect an ApiException when using invalid credentials
 */
class UseClientCredentialsSpec extends AbstractIntegrationTest
{
    /**
     *
     * @return array{clientId: string, clientSecret: string}
     * @throws Exception
     */
    public function generateUserSecret(string $token, string $loginName = 'api-user'): array
    {
        $userIdResponse = @file_get_contents(
            'http://localhost:8099/management/v1/global/users/_by_login_name?loginName=' . urlencode($loginName),
            false,
            stream_context_create(['http' => [
                'header' => "Authorization: Bearer $token\r\nAccept: application/json",
                'ignore_errors' => true
            ]])
        );

        if ($userIdResponse !== false) {
            $userId = json_decode($userIdResponse, true)['user']['id'] ?? null;

            if ($userId) {
                $secretResponse = @file_get_contents(
                    "http://localhost:8099/management/v1/users/$userId/secret",
                    false,
                    stream_context_create([
                        'http' => [
                            'method'  => 'PUT',
                            'header'  => "Authorization: Bearer $token\r\n" .
                                "Content-Type: application/json\r\n" .
                                "Accept: application/json",
                            'content' => '{}',
                            'ignore_errors' => true
                        ]
                    ])
                );

                if ($secretResponse !== false) {
                    $secretData = json_decode($secretResponse, true);
                    $clientId = $secretData['clientId'] ?? null;
                    $clientSecret = $secretData['clientSecret'] ?? null;

                    if ($clientId && $clientSecret) {
                        return [
                            'clientId' => $clientId,
                            'clientSecret' => $clientSecret
                        ];
                    } else {
                        print_r($secretResponse);
                        throw new Exception("API response for secret is missing 'clientId' or 'clientSecret'.");
                    }
                } else {
                    throw new Exception("API call to generate secret failed for user ID: '$userId'.");
                }
            } else {
                print_r($userIdResponse);
                throw new Exception("Could not parse a valid user ID from API response for login name: '$loginName'.");
            }
        } else {
            throw new Exception("API call to retrieve user failed for login name: '$loginName'.");
        }
    }

    /**
     * Validate retrieval of general settings with valid client credentials.
     *
     * @throws ApiException on API error
     * @throws Exception
     * @doesNotPerformAssertions
     */
    public function testRetrievesGeneralSettingsWithValidAuth(): void
    {
        $credentials = $this->generateUserSecret(self::getAuthToken());
        $client = Zitadel::withClientCredentials(self::getBaseUrl(), $credentials['clientId'], $credentials['clientSecret']);

        $client->settings->settingsServiceGetGeneralSettings();
    }

    /**
     * Expect an ApiException when using invalid client credentials.
     * @throws Exception
     */
    public function testRaisesApiExceptionWithInvalidAuth(): void
    {
        $invalid = Zitadel::withClientCredentials(self::getBaseUrl(), 'invalid', 'invalid');

        $this->expectException(ZitadelException::class);
        $invalid->settings->settingsServiceGetGeneralSettings();
    }
}
