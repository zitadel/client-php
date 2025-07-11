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
     * @param string $token The token to be used for authentication.
     */
    public function __construct(string $host = 'localhost', private readonly string $token = '')
    {
        parent::__construct($host);
    }

    /**
     * Retrieves the authentication token needed for API requests.
     *
     * @return string The authentication token.
     */
    public function getAuthToken(): string
    {
        return $this->token;
    }
}
