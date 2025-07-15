<?php

namespace Zitadel\Client\Api;

use Zitadel\Client\IApiClient;

class BetaOIDCServiceApi
{

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(private readonly IApiClient $apiClient)
    {
        //
    }
}

