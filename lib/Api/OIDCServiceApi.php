<?php

namespace Zitadel\Client\Api;

use Zitadel\Client\IApiClient;

class OIDCServiceApi
{

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(private readonly IApiClient $apiClient)
    {
        //
    }
}

