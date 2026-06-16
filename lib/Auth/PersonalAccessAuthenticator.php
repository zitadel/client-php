<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

/**
 * Personal Access Token Authenticator.
 *
 * Uses a static personal access token (PAT) for API authentication. A PAT is
 * a long-lived bearer credential minted out-of-band in the Zitadel console, so
 * no token exchange is required: the token is attached verbatim on every
 * request. This authenticator therefore implements {@see Authenticator}
 * directly and does NOT need {@see HttpAwareAuthenticator}.
 */
class PersonalAccessAuthenticator extends BaseAuthenticator
{
    private readonly string $host;

    /**
     * PersonalAccessAuthenticator constructor.
     *
     * @param string $host  The base URL for the API endpoints.
     * @param string $token The personal access token.
     */
    public function __construct(
        string $host,
        private readonly string $token
    ) {
        $this->host = $host;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Retrieve the personal access token.
     *
     * Retained for backward compatibility and direct inspection; the value is
     * the same credential emitted in the Authorization header.
     *
     * @return string The authentication token.
     */
    public function getAuthToken(): string
    {
        return $this->token;
    }

    /**
     * @return array<string, string>
     */
    public function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    /**
     * Masks the token so it never leaks through var_dump() / print_r() /
     * stack traces / error logs.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return [
            'host' => $this->host,
            'token' => '***',
        ];
    }
}
