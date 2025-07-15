<?php

namespace Zitadel\Client\Api;

use Zitadel\Client\IApiClient;

class BetaAppServiceApi
{

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(private readonly IApiClient $apiClient)
    {
        //
    }
}
