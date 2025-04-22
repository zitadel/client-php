<?php

namespace Zitadel\Client\Spec;

use Exception;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use Zitadel\Client\Model\UserServiceSetHumanEmail;
use Zitadel\Client\Model\UserServiceSetHumanProfile;
use Zitadel\Client\Zitadel;

class SDKTestUsingWebTokenAssertionAuthenticationSpec extends TestCase
{
    private string $keyFile;
    private string $baseUrl;
    private string $userId;

    /**
     * @throws Exception
     */
    public function testDeactivateUserWithValidToken(): void
    {
        $zitadel = Zitadel::withPrivateKey($this->baseUrl, $this->keyFile);

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
    protected function setUp(): void
    {
        $this->baseUrl = getenv('BASE_URL') ?: ($_ENV['BASE_URL'] ?? null);
        $this->keyFile = SDKTestUsingWebTokenAssertionAuthenticationSpec::createTempJwtFile();
        $this->userId = $this->createUser();
    }

    private static function createTempJwtFile(): string
    {
        $k = getenv('JWT_KEY') ?: ($_ENV['JWT_KEY'] ?? null);
        $p = tempnam(sys_get_temp_dir(), 'jwt_') or exit;
        file_put_contents($p, $k) or exit;
        return $p;
    }

    /**
     * @throws Exception
     */
    private function createUser(): string
    {
        $zitadel = Zitadel::withPrivateKey($this->baseUrl, $this->keyFile);

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
