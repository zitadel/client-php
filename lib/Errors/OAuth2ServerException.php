<?php

declare(strict_types=1);

namespace Zitadel\Client\Errors;

use Zitadel\Client\ZitadelException;

/**
 * Exception for an OAuth2 token endpoint that answered with a non-2xx status.
 *
 * Carries the RFC 6749 section 5.2 error fields when the response body holds
 * a well-formed OAuth2 error object, and the raw body in every case. The
 * error code is `errorCode` because `\Exception::$code` is taken.
 */
class OAuth2ServerException extends ZitadelException
{
    /**
     * @param int $statusCode The HTTP status code of the token response.
     * @param string|null $errorCode The RFC 6749 error code, or null when the body held no OAuth2 error object.
     * @param string|null $description The human-readable error description, if present.
     * @param string|null $uri A URI describing the error, if present.
     * @param string $rawBody The raw token response body.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $errorCode,
        public readonly ?string $description,
        public readonly ?string $uri,
        public readonly string $rawBody,
    ) {
        if ($errorCode === null) {
            $message = 'Token request failed with status ' . $statusCode . ': ' . $rawBody;
        } elseif ($description !== null) {
            $message = 'Token request failed with status ' . $statusCode . ': ' . $errorCode . ' -- ' . $description;
        } else {
            $message = 'Token request failed with status ' . $statusCode . ': ' . $errorCode;
        }
        parent::__construct($message);
    }
}
