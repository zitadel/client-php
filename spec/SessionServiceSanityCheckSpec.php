<?php

namespace Zitadel\Client\Spec;

use Zitadel\Client\ApiException;
use Zitadel\Client\Models\SessionServiceChecks;
use Zitadel\Client\Models\SessionServiceCheckUser;
use Zitadel\Client\Models\SessionServiceCreateSessionRequest;
use Zitadel\Client\Models\SessionServiceDeleteSessionRequest;
use Zitadel\Client\Models\SessionServiceGetSessionRequest;
use Zitadel\Client\Models\SessionServiceListSessionsRequest;
use Zitadel\Client\Models\SessionServiceSetSessionRequest;
use Zitadel\Client\Models\UserServiceAddHumanUserRequest;
use Zitadel\Client\Models\UserServiceSetHumanEmail;
use Zitadel\Client\Models\UserServiceSetHumanProfile;
use Zitadel\Client\Zitadel;

/**
 * SessionService Integration Tests
 *
 * This suite verifies the Zitadel SessionService API's basic operations using a
 * personal access token:
 *
 *  1. Create a session with specified checks and lifetime
 *  2. Retrieve the session by ID
 *  3. List sessions and ensure the created session appears
 *  4. Update the session's lifetime and confirm a new token is returned
 *  5. Error when retrieving a non-existent session
 *
 * Each test runs in isolation: a new session is created in setUp() and deleted in
 * tearDown() to ensure a clean state.
 */
class SessionServiceSanityCheckSpec extends AbstractIntegrationTest
{
    private static Zitadel $client;
    private string $sessionId;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client = Zitadel::withAccessToken(self::getBaseUrl(), self::getAuthToken());
    }

    /**
     * @throws ApiException
     */
    public function testRetrievesTheSessionDetailsById(): void
    {
        $request = new SessionServiceGetSessionRequest();
        $request->sessionId = $this->sessionId;

        $response = self::$client->sessions->getSession(
            $request,
        );
        $this->assertNotNull($response->session);
        $this->assertSame(
            $this->sessionId,
            $response->session->id
        );
    }

    /**
     * @throws ApiException
     */
    public function testIncludesTheCreatedSessionWhenListingAllSessions(): void
    {
        $request = new SessionServiceListSessionsRequest();
        $request->queries = new \Ds\Vector();

        $response = self::$client->sessions->listSessions($request);
        $this->assertNotNull($response->sessions);

        $ids = [];
        foreach ($response->sessions as $session) {
            $ids[] = $session->id;
        }

        $this->assertContains($this->sessionId, $ids);
    }

    /**
     * @throws ApiException
     */
    public function testUpdatesTheSessionLifetimeAndReturnsANewToken(): void
    {
        $request = new SessionServiceSetSessionRequest();
        $request->sessionId = $this->sessionId;
        $request->lifetime = new \DateInterval('PT36000S');

        $response = self::$client->sessions->setSession(
            $request
        );
        $this->assertIsString($response->sessionToken);
    }

    public function testRaisesAnApiExceptionWhenRetrievingANonExistentSession(): void
    {
        $request = new SessionServiceGetSessionRequest();
        $request->sessionId = uniqid();

        $this->expectException(ApiException::class);
        self::$client->sessions->getSession(
            $request,
        );
    }

    /**
     * @throws ApiException
     */
    protected function setUp(): void
    {
        $id = uniqid('user_');

        $profile = new UserServiceSetHumanProfile();
        $profile->givenName = 'John';
        $profile->familyName = 'Doe';

        $email = new UserServiceSetHumanEmail();
        $email->email = 'johndoe' . uniqid() . '@example.com';

        $request = new UserServiceAddHumanUserRequest();
        $request->username = $id;
        $request->profile = $profile;
        $request->email = $email;

        self::$client->users->addHumanUser($request);

        $checkUser = new SessionServiceCheckUser();
        $checkUser->loginName = $id;

        $checks = new SessionServiceChecks();
        $checks->user = $checkUser;

        $sessionRequest = new SessionServiceCreateSessionRequest();
        $sessionRequest->checks = $checks;
        $sessionRequest->lifetime = new \DateInterval('PT18000S');

        $response = self::$client->sessions->createSession($sessionRequest);
        $this->assertNotNull($response->sessionId);
        $this->sessionId = $response->sessionId;
    }

    protected function tearDown(): void
    {
        $request = new SessionServiceDeleteSessionRequest();
        $request->sessionId = $this->sessionId;

        try {
            self::$client->sessions->deleteSession(
                $request
            );
        } catch (ApiException) {
            // Ignore cleanup errors
        }
    }
}
