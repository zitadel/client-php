<?php

namespace Zitadel\Client\Api;

use InvalidArgumentException;
use Zitadel\Client\IApiClient;
use Zitadel\Client\Model;
use Zitadel\Client\ZitadelException;

class UserServiceApi
{
    private IApiClient $apiClient;

    public function __construct(IApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @throws ZitadelException
     */
    public function addHumanUser(Model\UserServiceAddHumanUserRequest $userServiceAddHumanUserRequest): ?object
    {
        return $this->addHumanUserInternal($userServiceAddHumanUserRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function addHumanUserInternal(Model\UserServiceAddHumanUserRequest $userServiceAddHumanUserRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($userServiceAddHumanUserRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $userServiceAddHumanUserRequest when calling addHumanUser');
        }

        return $this->apiClient->invokeAPI(
            'addHumanUser',
            '/zitadel.user.v2.UserService/AddHumanUser',
            'POST',
            [],
            [],
            [],
            $userServiceAddHumanUserRequest,
            Model\UserServiceAddHumanUserResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function deleteUser(Model\UserServiceDeleteUserRequest $userServiceDeleteUserRequest): ?object
    {
        return $this->deleteUserInternal($userServiceDeleteUserRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function deleteUserInternal(Model\UserServiceDeleteUserRequest $userServiceDeleteUserRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($userServiceDeleteUserRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $userServiceDeleteUserRequest when calling deleteUser');
        }

        return $this->apiClient->invokeAPI(
            'deleteUser',
            '/zitadel.user.v2.UserService/DeleteUser',
            'POST',
            [],
            [],
            [],
            $userServiceDeleteUserRequest,
            Model\UserServiceDeleteUserResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function getUserByID(Model\UserServiceGetUserByIDRequest $userServiceGetUserByIDRequest): ?object
    {
        return $this->getUserByIDInternal($userServiceGetUserByIDRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function getUserByIDInternal(Model\UserServiceGetUserByIDRequest $userServiceGetUserByIDRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($userServiceGetUserByIDRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $userServiceGetUserByIDRequest when calling getUserByID');
        }

        return $this->apiClient->invokeAPI(
            'getUserByID',
            '/zitadel.user.v2.UserService/GetUserByID',
            'POST',
            [],
            [],
            [],
            $userServiceGetUserByIDRequest,
            Model\UserServiceGetUserByIDResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function listUsers(Model\UserServiceListUsersRequest $userServiceListUsersRequest): ?object
    {
        return $this->listUsersInternal($userServiceListUsersRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function listUsersInternal(Model\UserServiceListUsersRequest $userServiceListUsersRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($userServiceListUsersRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $userServiceListUsersRequest when calling listUsers');
        }

        return $this->apiClient->invokeAPI(
            'listUsers',
            '/zitadel.user.v2.UserService/ListUsers',
            'POST',
            [],
            [],
            [],
            $userServiceListUsersRequest,
            Model\UserServiceListUsersResponse::class
        );
    }

    /**
     * @throws ZitadelException
     */
    public function updateHumanUser(Model\UserServiceUpdateHumanUserRequest $userServiceUpdateHumanUserRequest): ?object
    {
        return $this->updateHumanUserInternal($userServiceUpdateHumanUserRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function updateHumanUserInternal(Model\UserServiceUpdateHumanUserRequest $userServiceUpdateHumanUserRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($userServiceUpdateHumanUserRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $userServiceUpdateHumanUserRequest when calling updateHumanUser');
        }

        return $this->apiClient->invokeAPI(
            'updateHumanUser',
            '/zitadel.user.v2.UserService/UpdateHumanUser',
            'POST',
            [],
            [],
            [],
            $userServiceUpdateHumanUserRequest,
            Model\UserServiceUpdateHumanUserResponse::class
        );
    }
}
