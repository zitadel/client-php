<?php

namespace Zitadel\Client\Spec;

use Zitadel\Client\ApiException;
use Zitadel\Client\Models\UserServiceAddHumanUserRequest;
use Zitadel\Client\Models\UserServiceAddHumanUserResponse;
use Zitadel\Client\Models\UserServiceDeleteUserRequest;
use Zitadel\Client\Models\UserServiceGetUserByIDRequest;
use Zitadel\Client\Models\UserServiceListUsersRequest;
use Zitadel\Client\Models\UserServiceSetHumanEmail;
use Zitadel\Client\Models\UserServiceSetHumanProfile;
use Zitadel\Client\Models\UserServiceUpdateHumanUserRequest;
use Zitadel\Client\Models\UserServiceUser;
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

    #[\Override]
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
        $request = new UserServiceGetUserByIDRequest();
        $request->userId = $this->user->userId;

        $response = self::$client->users->getUserByID($request);
        $this->assertNotNull($response->user);
        $this->assertSame(
            $this->user->userId,
            $response->user->userId
        );
    }

    /**
     * List all human users and verify the created user appears in the list.
     *
     * @throws ApiException on API error
     */
    public function testIncludesTheCreatedUserWhenListingAllUsers(): void
    {
        $request = new UserServiceListUsersRequest();
        $request->queries = new \Ds\Vector();

        $response = self::$client->users->listUsers($request);
        $this->assertNotNull($response->result);

        $ids = [];
        foreach ($response->result as $userItem) {
            /** @var UserServiceUser $userItem */
            $ids[] = $userItem->userId;
        }

        $this->assertContains(
            $this->user->userId,
            $ids
        );
    }

    /**
     * Update the user's email and verify via a get call that the change was applied.
     *
     * @throws ApiException on API error
     */
    public function testUpdatesTheUserEmailAndReflectsInGet(): void
    {
        $email = new UserServiceSetHumanEmail();
        $email->email = 'updated' . uniqid() . '@example.com';

        $update = new UserServiceUpdateHumanUserRequest();
        $update->userId = $this->user->userId;
        $update->email = $email;

        self::$client->users->updateHumanUser($update);

        $request = new UserServiceGetUserByIDRequest();
        $request->userId = $this->user->userId;

        $response = self::$client->users->getUserByID($request);
        $this->assertNotNull($response->user);
        $this->assertNotNull($response->user->human);
        $this->assertNotNull($response->user->human->email);
        $this->assertNotNull($response->user->human->email->email);
        $this->assertStringContainsString(
            'updated',
            $response->user->human->email->email
        );
    }

    /**
     * Attempt to retrieve a non-existent user and expect an ApiException.
     */
    public function testRaisesAnApiExceptionWhenRetrievingNonExistentUser(): void
    {
        $this->expectException(ApiException::class);

        $request = new UserServiceGetUserByIDRequest();
        $request->userId = uniqid();
        self::$client->users->getUserByID($request);
    }

    /**
     * Create a new human user before each test.
     *
     * @throws ApiException on API error
     */
    protected function setUp(): void
    {
        $profile = new UserServiceSetHumanProfile();
        $profile->givenName = 'John';
        $profile->familyName = 'Doe';

        $email = new UserServiceSetHumanEmail();
        $email->email = 'johndoe' . uniqid() . '@example.com';

        $request = new UserServiceAddHumanUserRequest();
        $request->username = uniqid('user_');
        $request->profile = $profile;
        $request->email = $email;

        $this->user = self::$client->users->addHumanUser($request);
    }

    /**
     * Remove the created human user after each test.
     */
    protected function tearDown(): void
    {
        try {
            $request = new UserServiceDeleteUserRequest();
            $request->userId = $this->user->userId;
            self::$client->users->deleteUser($request);
        } catch (ApiException) {
            // cleanup errors ignored
        }
    }
}
