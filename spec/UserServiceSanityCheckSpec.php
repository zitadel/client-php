<?php

namespace Zitadel\Client\Spec;

use Zitadel\Client\ApiException;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use Zitadel\Client\Model\UserServiceAddHumanUserResponse;
use Zitadel\Client\Model\UserServiceDeleteUserRequest;
use Zitadel\Client\Model\UserServiceGetUserByIDRequest;
use Zitadel\Client\Model\UserServiceListUsersRequest;
use Zitadel\Client\Model\UserServiceSetHumanEmail;
use Zitadel\Client\Model\UserServiceSetHumanProfile;
use Zitadel\Client\Model\UserServiceUpdateHumanUserRequest;
use Zitadel\Client\Model\UserServiceUser;
use Zitadel\Client\Zitadel;

/**
 * UserService Integration Tests
 *
 * This suite verifies the Zitadel UserService API's basic operations using a
 * personal access token:
 *
 *  1. Create a human user
 *  2. Retrieve the user by ID
 *  3. List users and ensure the created user appears
 *  4. Update the user's email and confirm change
 *  5. Error when retrieving a non-existent user
 *
 * Each test runs in isolation: a new user is created in setUp() and removed in
 * tearDown() to ensure a clean state.
 */
class UserServiceSanityCheckSpec extends AbstractIntegrationTest
{
    protected static Zitadel $client;
    protected UserServiceAddHumanUserResponse $user;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client = Zitadel::withAccessToken(self::getBaseUrl(), self::getAuthToken());
    }

    /**
     * Retrieve the user by ID and verify the returned ID matches.
     *
     * @throws ApiException on API error
     */
    public function testRetrievesTheUserDetailsById(): void
    {
        $response = self::$client->users->getUserByID(
            (new UserServiceGetUserByIDRequest())
            ->setUserId($this->user->getUserId())
        );
        $this->assertSame(
            $this->user->getUserId(),
            $response->getUser()->getUserId()
        );
    }

    /**
     * List all human users and verify the created user appears in the list.
     *
     * @throws ApiException on API error
     */
    public function testIncludesTheCreatedUserWhenListingAllUsers(): void
    {
        $request = (new UserServiceListUsersRequest())
            ->setQueries([]);

        $response = self::$client->users->listUsers($request);
        $this->assertContains(
            $this->user->getUserId(),
            array_map(
                fn (UserServiceUser $userItem): string => $userItem->getUserId(),
                $response->getResult()
            )
        );
    }

    /**
     * Update the user's email and verify via a get call that the change was applied.
     *
     * @throws ApiException on API error
     */
    public function testUpdatesTheUserEmailAndReflectsInGet(): void
    {
        self::$client->users->updateHumanUser(
            (new UserServiceUpdateHumanUserRequest())
                ->setUserId($this->user->getUserId())
                ->setEmail(
                    (new UserServiceSetHumanEmail())
                        ->setEmail('updated' . uniqid() . '@example.com')
                )
        );

        $response = self::$client->users->getUserByID(
            (new UserServiceGetUserByIDRequest())->setUserId($this->user->getUserId())
        );
        $this->assertStringContainsString(
            'updated',
            $response->getUser()->getHuman()->getEmail()
        );
    }

    /**
     * Attempt to retrieve a non-existent user and expect an ApiException.
     */
    public function testRaisesAnApiExceptionWhenRetrievingNonExistentUser(): void
    {
        $this->expectException(ApiException::class);
        self::$client->users->getUserByID((new UserServiceGetUserByIDRequest())->setUserId(uniqid()));
    }

    /**
     * Create a new human user before each test.
     *
     * @throws ApiException on API error
     */
    protected function setUp(): void
    {
        $request = (new UserServiceAddHumanUserRequest())
            ->setUsername(uniqid('user_'))
            ->setProfile(
                (new UserServiceSetHumanProfile())
                    ->setGivenName('John')
                    ->setFamilyName('Doe')
            )
            ->setEmail(
                (new UserServiceSetHumanEmail())
                    ->setEmail('johndoe' . uniqid() . '@example.com')
            );

        $this->user = self::$client->users->addHumanUser($request);
    }

    /**
     * Remove the created human user after each test.
     */
    protected function tearDown(): void
    {
        try {
            self::$client->users->deleteUser(
                (new UserServiceDeleteUserRequest())->setUserId($this->user->getUserId())
            );
        } catch (ApiException) {
            // cleanup errors ignored
        }
    }
}
