<?php

namespace Zitadel\Client\Auth;

/**
 * Dummy Authenticator for testing purposes.
 *
 * This authenticator does not apply any authentication to API requests.
 */
class NoAuthAuthenticator extends Authenticator
{
    /**
     * NoAuthAuthenticator constructor.
     *
     * @param string $host The base URL for all authentication endpoints.
     */
    public function __construct(string $host = 'localhost')
    {
        parent::__construct($host);
    }

    /**
     * Retrieve the authentication token needed for API requests.
     *
     * @return string The authentication token
     */
    public function getAuthToken(): string
    {
        return "";
    }
}
