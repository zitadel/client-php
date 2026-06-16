<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

/**
 * No-op Authenticator for testing and unauthenticated endpoints.
 *
 * Applies no authentication to API requests. Implements {@see Authenticator}
 * directly: it has no host-dependent state and never mints a token.
 */
class NoAuthAuthenticator extends BaseAuthenticator
{
    private readonly string $host;

    /**
     * NoAuthAuthenticator constructor.
     *
     * @param string $host The base URL for the API endpoints.
     */
    public function __construct(string $host = 'localhost')
    {
        $this->host = $host;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Retrieve the authentication token.
     *
     * Retained for backward compatibility; always empty since this
     * authenticator never authenticates.
     *
     * @return string Always an empty string.
     */
    public function getAuthToken(): string
    {
        return "";
    }

    /**
     * @return array<string, string>
     */
    public function getAuthHeaders(): array
    {
        return [];
    }
}
