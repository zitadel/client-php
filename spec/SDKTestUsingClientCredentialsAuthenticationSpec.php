<?php

namespace Zitadel\Client\Spec;

use Exception;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use Zitadel\Client\Model\UserServiceSetHumanEmail;
use Zitadel\Client\Model\UserServiceSetHumanProfile;
use Zitadel\Client\Zitadel;

class SDKTestUsingClientCredentialsAuthenticationSpec extends TestCase
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private string $userId;

    /**
     * @throws Exception
     */
    public function testDeactivateUserWithValidToken(): void
    {
        $zitadel = Zitadel::withClientCredentials($this->baseUrl, $this->clientId, $this->clientSecret);

        try {
            $deactivateResponse = $zitadel->users->userServiceDeactivateUser($this->userId);
            // @phpstan-ignore-next-line
            $this->assertNotNull($deactivateResponse, 'User should be deactivated');
            echo "User deactivated: " . $deactivateResponse . "\n";

            $reactivateResponse = $zitadel->users->userServiceReactivateUser($this->userId);
            // @phpstan-ignore-next-line
            $this->assertNotNull($reactivateResponse, 'User should be reactivated');
            echo "User reactivated: " . $reactivateResponse . "\n";
        } catch (ApiException $e) {
            $this->fail('Error deactivating/reactivating user: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testDeactivateUserWithInvalidToken(): void
    {
        $zitadel = Zitadel::withClientCredentials($this->baseUrl, "", "");

        try {
            $zitadel->users->userServiceDeactivateUser($this->userId);
            $this->fail('Expected exception when deactivating user with invalid token');
        } catch (Exception $e) {
            $this->assertStringContainsString('invalid_request', $e->getMessage());
            echo "Caught expected ApiException for deactivating user with invalid token: " . $e->getMessage() . "\n";
        }

        try {
            $zitadel->users->userServiceReactivateUser($this->userId);
            $this->fail('Expected exception when reactivating user with invalid token');
        } catch (Exception $e) {
            $this->assertStringContainsString('invalid_request', $e->getMessage());
            echo "Caught expected ApiException for reactivating user with invalid token: " . $e->getMessage() . "\n";
        }
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->baseUrl = getenv('BASE_URL') ?: ($_ENV['BASE_URL'] ?? null);
        $this->clientId = getenv('CLIENT_ID') ?: ($_ENV['CLIENT_ID'] ?? null);
        $this->clientSecret = getenv('CLIENT_SECRET') ?: ($_ENV['CLIENT_SECRET'] ?? null);
        $this->userId = $this->createUser();
    }

    /**
     * @throws Exception
     */
    private function createUser(): string
    {
        $zitadel = Zitadel::withClientCredentials($this->baseUrl, $this->clientId, $this->clientSecret);

        $request = new UserServiceAddHumanUserRequest();
        $request->setUsername(uniqid('user_'))
          ->setProfile((new UserServiceSetHumanProfile())
            ->setGivenName('John')
            ->setFamilyName('Doe'))
          ->setEmail((new UserServiceSetHumanEmail())
            ->setEmail('johndoe' . uniqid() . '@example.com'));

        try {
            $response = $zitadel->users->userServiceAddHumanUser($request);
            return $response->getUserId();
        } catch (ApiException $e) {
            $this->fail('Error creating user: ' . $e->getMessage());
        }
    }
}
