<?php

namespace Zitadel\Client\Spec;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use Zitadel\Client\Model\UserServiceSetHumanEmail;
use Zitadel\Client\Model\UserServiceSetHumanProfile;
use Zitadel\Client\Zitadel;

class SDKTestUsingPersonalAccessTokenAuthenticationSpec extends TestCase
{
    private string $validToken;
    private string $baseUrl;
    private string $userId;

    public function testDeactivateUserWithValidToken(): void
    {
        $zitadel = Zitadel::withAccessToken($this->baseUrl, $this->validToken);

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

    public function testDeactivateUserWithInvalidToken(): void
    {
        $zitadel = Zitadel::withAccessToken($this->baseUrl, "invalid");

        try {
            $zitadel->users->userServiceDeactivateUser($this->userId);
            $this->fail('Expected exception when deactivating user with invalid token');
        } catch (ApiException $e) {
            $this->assertStringContainsString('Unauthorized', $e->getMessage());
            echo "Caught expected ApiException for deactivating user with invalid token: " . $e->getMessage() . "\n";
        }

        try {
            $zitadel->users->userServiceReactivateUser($this->userId);
            $this->fail('Expected exception when reactivating user with invalid token');
        } catch (ApiException $e) {
            $this->assertStringContainsString('Unauthorized', $e->getMessage());
            echo "Caught expected ApiException for reactivating user with invalid token: " . $e->getMessage() . "\n";
        }
    }

    protected function setUp(): void
    {
        $this->baseUrl = getenv('BASE_URL') ?: ($_ENV['BASE_URL'] ?? null);
        $this->validToken = getenv('AUTH_TOKEN') ?: ($_ENV['AUTH_TOKEN'] ?? null);
        $this->userId = $this->createUser();
    }

    private function createUser(): string
    {
        $zitadel = Zitadel::withAccessToken($this->baseUrl, $this->validToken);

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
