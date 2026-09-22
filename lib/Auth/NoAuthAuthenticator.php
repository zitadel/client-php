<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;

/**
 * A no-op authenticator that performs no authentication.
 *
 * Useful for testing and unauthenticated endpoints: it never mints a token,
 * so it returns an empty set of auth headers.
 */
class NoAuthAuthenticator extends BaseAuthenticator
{
    private readonly string $host;

    /**
     * @param string $host The base URL for the API endpoints.
     * @throws InvalidArgumentException If the host is not a valid http or https URL.
     */
    public function __construct(string $host = 'http://localhost')
    {
        $this->host = new OpenId($host)->getHostEndpoint();
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
        return [];
    }
}
