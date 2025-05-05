<?php

namespace Zitadel\Client\Spec;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;
use Zitadel\Client\Model\SessionServiceChecks;
use Zitadel\Client\Model\SessionServiceCheckUser;
use Zitadel\Client\Model\SessionServiceCreateSessionRequest;
use Zitadel\Client\Model\SessionServiceDeleteSessionRequest;
use Zitadel\Client\Model\SessionServiceListSessionsRequest;
use Zitadel\Client\Model\SessionServiceSetSessionRequest;
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
class SessionServiceSanityCheckSpec extends TestCase
{
    private static string $validToken;
    private static string $baseUrl;
    private static Zitadel $client;
    private string $sessionId;

    public static function setUpBeforeClass(): void
    {
        self::$validToken = self::env('AUTH_TOKEN');
        self::$baseUrl = self::env('BASE_URL');
        self::$client = Zitadel::withAccessToken(self::$baseUrl, self::$validToken);
    }

    /**
     * Retrieve a configuration variable from the environment, falling back to $_ENV.
     */
    private static function env(string $key): string
    {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }

    /**
     * @throws ApiException
     */
    public function testRetrievesTheSessionDetailsById(): void
    {
        $response = self::$client->sessions->sessionServiceGetSession(
            $this->sessionId,
        );
        $this->assertSame(
            $this->sessionId,
            $response->getSession()->getId()
        );
    }

    /**
     * @throws ApiException
     */
    public function testIncludesTheCreatedSessionWhenListingAllSessions(): void
    {
        $request = (new SessionServiceListSessionsRequest())
            ->setQueries([]);

        $response = self::$client->sessions->sessionServiceListSessions($request);
        $ids = array_map(
            fn ($session) => $session->getId(),
            $response->getSessions()
        );

        $this->assertContains($this->sessionId, $ids);
    }

    /**
     * @throws ApiException
     */
    public function testUpdatesTheSessionLifetimeAndReturnsANewToken(): void
    {
        $request = (new SessionServiceSetSessionRequest())
            ->setLifetime('36000s');

        $response = self::$client->sessions->sessionServiceSetSession(
            $this->sessionId,
            $request
        );
        $this->assertIsString($response->getSessionToken());
    }

    public function testRaisesAnApiExceptionWhenRetrievingANonExistentSession(): void
    {
        $this->expectException(ApiException::class);
        self::$client->sessions->sessionServiceGetSession(
            uniqid()
        );
    }

    /**
     * @throws ApiException
     */
    protected function setUp(): void
    {
        $request = new SessionServiceCreateSessionRequest();
        $request->setChecks(
            (new SessionServiceChecks())
                ->setUser(
                    (new SessionServiceCheckUser())
                        ->setLoginName('johndoe')
                )
        );
        $request->setLifetime('18000s');

        $response = self::$client->sessions->sessionServiceCreateSession($request);
        $this->sessionId = $response->getSessionId();
    }

    protected function tearDown(): void
    {
        $request = new SessionServiceDeleteSessionRequest();
        try {
            self::$client->sessions->sessionServiceDeleteSession(
                $this->sessionId,
                $request
            );
        } catch (ApiException) {
            // Ignore cleanup errors
        }
    }
}
