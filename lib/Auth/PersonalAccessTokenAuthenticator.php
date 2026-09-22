<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;

/**
 * Personal Access Token Authenticator.
 *
 * Uses a static personal access token (PAT) for API authentication. A PAT is a
 * long-lived bearer credential minted out-of-band in the Zitadel console, so
 * no token exchange is required: the token is attached verbatim on every
 * request.
 */
class PersonalAccessTokenAuthenticator extends BaseAuthenticator
{
    private readonly string $host;

    private readonly string $token;

    /**
     * @param string $host  The base URL for the API endpoints.
     * @param string $token The personal access token.
     * @throws InvalidArgumentException If the host is not a valid http or https
     *                                  URL or the token is empty.
     */
    public function __construct(string $host, string $token)
    {
        $this->host = new OpenId($host)->getHostEndpoint();
        $this->token = OAuthAuthenticator::requireText($token, 'Token');
    }

    #[\Override]
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    /**
     * Redacts the token from var_dump() / print_r() output.
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
