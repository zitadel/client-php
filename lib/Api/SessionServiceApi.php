<?php /** @noinspection PhpUnused */

namespace Zitadel\Client\Api;

use InvalidArgumentException;
use Zitadel\Client\IApiClient;
use Zitadel\Client\Model;
use Zitadel\Client\ZitadelException;

class SessionServiceApi
{
    private IApiClient $apiClient;

    public function __construct(IApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @throws ZitadelException
     */
    public function createSession(Model\SessionServiceCreateSessionRequest $sessionServiceCreateSessionRequest): ?object
    {
        return $this->createSessionInternal($sessionServiceCreateSessionRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function createSessionInternal(Model\SessionServiceCreateSessionRequest $sessionServiceCreateSessionRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($sessionServiceCreateSessionRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $sessionServiceCreateSessionRequest when calling createSession');
        }

        return $this->apiClient->invokeAPI(
            'createSession',
            '/zitadel.session.v2.SessionService/CreateSession',
            'POST',
            [],
            [],
            [],
            $sessionServiceCreateSessionRequest,
            Model\SessionServiceCreateSessionResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function deleteSession(Model\SessionServiceDeleteSessionRequest $sessionServiceDeleteSessionRequest): ?object
    {
        return $this->deleteSessionInternal($sessionServiceDeleteSessionRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function deleteSessionInternal(Model\SessionServiceDeleteSessionRequest $sessionServiceDeleteSessionRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($sessionServiceDeleteSessionRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $sessionServiceDeleteSessionRequest when calling deleteSession');
        }

        return $this->apiClient->invokeAPI(
            'deleteSession',
            '/zitadel.session.v2.SessionService/DeleteSession',
            'POST',
            [],
            [],
            [],
            $sessionServiceDeleteSessionRequest,
            Model\SessionServiceDeleteSessionResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function getSession(Model\SessionServiceGetSessionRequest $sessionServiceGetSessionRequest): ?object
    {
        return $this->getSessionInternal($sessionServiceGetSessionRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function getSessionInternal(Model\SessionServiceGetSessionRequest $sessionServiceGetSessionRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($sessionServiceGetSessionRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $sessionServiceGetSessionRequest when calling getSession');
        }

        return $this->apiClient->invokeAPI(
            'getSession',
            '/zitadel.session.v2.SessionService/GetSession',
            'POST',
            [],
            [],
            [],
            $sessionServiceGetSessionRequest,
            Model\SessionServiceGetSessionResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function listSessions(Model\SessionServiceListSessionsRequest $sessionServiceListSessionsRequest): ?object
    {
        return $this->listSessionsInternal($sessionServiceListSessionsRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function listSessionsInternal(Model\SessionServiceListSessionsRequest $sessionServiceListSessionsRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($sessionServiceListSessionsRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $sessionServiceListSessionsRequest when calling listSessions');
        }

        return $this->apiClient->invokeAPI(
            'listSessions',
            '/zitadel.session.v2.SessionService/ListSessions',
            'POST',
            [],
            [],
            [],
            $sessionServiceListSessionsRequest,
            Model\SessionServiceListSessionsResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function setSession(Model\SessionServiceSetSessionRequest $sessionServiceSetSessionRequest): ?object
    {
        return $this->setSessionInternal($sessionServiceSetSessionRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function setSessionInternal(Model\SessionServiceSetSessionRequest $sessionServiceSetSessionRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($sessionServiceSetSessionRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $sessionServiceSetSessionRequest when calling setSession');
        }

        return $this->apiClient->invokeAPI(
            'setSession',
            '/zitadel.session.v2.SessionService/SetSession',
            'POST',
            [],
            [],
            [],
            $sessionServiceSetSessionRequest,
            Model\SessionServiceSetSessionResponse::class
        );
    }
}
