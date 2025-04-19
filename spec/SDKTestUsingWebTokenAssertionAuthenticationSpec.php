<?php

namespace Zitadel\Client\Spec;

use Exception;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Auth\WebTokenAuthenticator;
use Zitadel\Client\Model\V2AddHumanUserRequest;
use Zitadel\Client\Model\V2SetHumanEmail;
use Zitadel\Client\Model\V2SetHumanProfile;
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

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->baseUrl = $_ENV['BASE_URL'] ? getenv('BASE_URL') : null;
        $this->keyFile = SDKTestUsingWebTokenAssertionAuthenticationSpec::createTempJwtFile();
        $this->userId = $this->createUser();
    }

    private static function createTempJwtFile(): string
    {
        $k = $_ENV['JWT_KEY'] ? getenv('JWT_KEY') : null;
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
