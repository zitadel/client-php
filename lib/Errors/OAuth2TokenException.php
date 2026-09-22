<?php

declare(strict_types=1);

namespace Zitadel\Client\Errors;

use Zitadel\Client\ZitadelException;

/**
 * Exception for an OAuth2 token endpoint that answered 2xx with a body the SDK
 * cannot use: not a JSON object, or without a non-empty `access_token`.
 */
class OAuth2TokenException extends ZitadelException
{
}
