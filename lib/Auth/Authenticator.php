<?php

namespace Zitadel\Client\Auth;

use League\Uri\Uri;

/**
 * Base abstract class for all authentication strategies.
 *
 * This class defines a standard interface for retrieving authentication headers
 * for API requests.
 */
abstract class Authenticator
{
    /**
     * The base URL for authentication endpoints.
     *
     * @var Uri
     */
    protected Uri $hostName;

    /**
     * Authenticator constructor.
     *
     * @param string $hostName The base URL for all authentication endpoints.
     */
    public function __construct(string $hostName)
    {
        $this->hostName = Uri::new($hostName);
    }

    /**
     * Retrieve the authentication token needed for API requests.
     *
     * @return string The authentication token
     */
    abstract public function getAuthToken(): string;

    /**
     * Retrieve the host URL.
     *
     * @return Uri The base URL for authentication endpoints.
     */
    public function getHost(): Uri
    {
        return $this->hostName;
    }
}
