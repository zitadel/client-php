<?php

namespace Zitadel\Client\Auth;

/**
 * Personal Access Token Authenticator.
 *
 * Uses a static personal access token for API authentication.
 */
class PersonalAccessAuthenticator extends Authenticator
{
    /**
     * PersonalAccessAuthenticator constructor.
     *
     * @param string $host The base URL for the API endpoints.
     * @param string $token The personal access token.
     */
    public function __construct(string         $host, /**
     * The personal access token.
     */
        private string $token)
    {
        parent::__construct($host);
    }

    /**
     * Retrieve authentication token using the personal access token.
     *
     * @return string The authentication token
     */
    public function getAuthToken(): string
    {
        return $this->token;
    }
}
