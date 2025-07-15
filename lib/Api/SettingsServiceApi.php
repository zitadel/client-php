<?php

namespace Zitadel\Client\Api;

use InvalidArgumentException;
use stdClass;
use Zitadel\Client\IApiClient;
use Zitadel\Client\Model;
use Zitadel\Client\ZitadelException;

class SettingsServiceApi
{
    private IApiClient $apiClient;

    public function __construct(IApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @throws ZitadelException
     */
    public function getGeneralSettings(object $body = new stdClass()): ?object
    {
        return $this->getGeneralSettingsInternal($body);
    }

    /**
     * @throws ZitadelException
     */
    private function getGeneralSettingsInternal(object $body): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($body === null) {
            throw new InvalidArgumentException('Missing the required parameter $body when calling getGeneralSettings');
        }

        return $this->apiClient->invokeAPI(
            'getGeneralSettings',
            '/zitadel.settings.v2.SettingsService/GetGeneralSettings',
            'POST',
            [],
            [],
            [],
            $body,
            Model\SettingsServiceGetGeneralSettingsResponse::class
        );
    }

    /** @noinspection PhpUnused */
    /**
     * @throws ZitadelException
     */
    public function getLoginSettings(Model\SettingsServiceGetLoginSettingsRequest $settingsServiceGetLoginSettingsRequest): ?object
    {
        return $this->getLoginSettingsInternal($settingsServiceGetLoginSettingsRequest);
    }

    /**
     * @throws ZitadelException
     */
    private function getLoginSettingsInternal(Model\SettingsServiceGetLoginSettingsRequest $settingsServiceGetLoginSettingsRequest): ?object
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($settingsServiceGetLoginSettingsRequest === null) {
            throw new InvalidArgumentException('Missing the required parameter $settingsServiceGetLoginSettingsRequest when calling getLoginSettings');
        }

        return $this->apiClient->invokeAPI(
            'getLoginSettings',
            '/zitadel.settings.v2.SettingsService/GetLoginSettings',
            'POST',
            [],
            [],
            [],
            $settingsServiceGetLoginSettingsRequest,
            Model\SettingsServiceGetLoginSettingsResponse::class
        );
    }
}
