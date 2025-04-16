<?php

namespace Zitadel\Client\Spec;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Model\V2AddHumanUserRequest;
use Zitadel\Client\Model\V2SetHumanEmail;
use Zitadel\Client\Model\V2SetHumanProfile;
use Zitadel\Client\Zitadel;

class SDKTestUsingPersonalAccessTokenAuthenticationSpec extends TestCase
{
  private string $validToken;
  private string $baseUrl;
  private string $userId;

  public function testDeactivateUserWithValidToken(): void
  {
    $zitadel = new Zitadel(new PersonalAccessAuthenticator($this->baseUrl, $this->validToken));

    try {
      $deactivateResponse = $zitadel->users->deactivateUser($this->userId);
      // @phpstan-ignore-next-line
      $this->assertNotNull($deactivateResponse, 'User should be deactivated');
      echo "User deactivated: " . $deactivateResponse . "\n";

      $reactivateResponse = $zitadel->users->reactivateUser($this->userId);
      // @phpstan-ignore-next-line
      $this->assertNotNull($reactivateResponse, 'User should be reactivated');
      echo "User reactivated: " . $reactivateResponse . "\n";
    } catch (ApiException $e) {
      $this->fail('Error deactivating/reactivating user: ' . $e->getMessage());
    }
  }

  public function testDeactivateUserWithInvalidToken(): void
  {
    $zitadel = new Zitadel(new PersonalAccessAuthenticator($this->baseUrl, "invalid"));

    try {
      $zitadel->users->deactivateUser($this->userId);
      $this->fail('Expected exception when deactivating user with invalid token');
    } catch (ApiException $e) {
      $this->assertStringContainsString('Unauthorized', $e->getMessage());
      echo "Caught expected ApiException for deactivating user with invalid token: " . $e->getMessage() . "\n";
    }

    try {
      $zitadel->users->reactivateUser($this->userId);
      $this->fail('Expected exception when reactivating user with invalid token');
    } catch (ApiException $e) {
      $this->assertStringContainsString('Unauthorized', $e->getMessage());
      echo "Caught expected ApiException for reactivating user with invalid token: " . $e->getMessage() . "\n";
    }
  }

  protected function setUp(): void
  {
    $this->baseUrl = $_ENV['BASE_URL'];
    $this->validToken = $_ENV['AUTH_TOKEN'];
    $this->userId = $this->createUser();
  }

  private function createUser(): string
  {
    $zitadel = new Zitadel(new PersonalAccessAuthenticator($this->baseUrl, $this->validToken));

    $request = new V2AddHumanUserRequest();
    $request->setUsername(uniqid('user_'))
      ->setProfile((new V2SetHumanProfile())
        ->setGivenName('John')
        ->setFamilyName('Doe'))
      ->setEmail((new V2SetHumanEmail())
        ->setEmail('johndoe' . uniqid() . '@example.com'));

    try {
      $response = $zitadel->users->addHumanUser($request);
      return $response->getUserId();
    } catch (ApiException $e) {
      $this->fail('Error creating user: ' . $e->getMessage());
    }
  }
}
