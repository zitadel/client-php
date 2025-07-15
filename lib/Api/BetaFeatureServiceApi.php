<?php

namespace Zitadel\Client\Api;

use Zitadel\Client\IApiClient;

class BetaFeatureServiceApi
{

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(private readonly IApiClient $apiClient)
    {
        //
    }
}
