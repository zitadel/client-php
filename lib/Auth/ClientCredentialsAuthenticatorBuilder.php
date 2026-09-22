<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;

/**
 * Builder for {@see ClientCredentialsAuthenticator}.
 */
final class ClientCredentialsAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
    private readonly string $clientId;

    private readonly string $clientSecret;

    /**
     * @param string $host         The base URL for the OAuth provider.
     * @param string $clientId     The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @throws InvalidArgumentException If the host is not a valid http or https
     *                                  URL, or the client identifier or secret is empty.
     */
    public function __construct(string $host, string $clientId, string $clientSecret)
    {
        parent::__construct($host);
        $this->clientId = OAuthAuthenticator::requireText($clientId, 'Client ID');
        $this->clientSecret = OAuthAuthenticator::requireText($clientSecret, 'Client secret');
    }

    /**
     * Builds the ClientCredentialsAuthenticator.
     */
    public function build(): ClientCredentialsAuthenticator
    {
        return new ClientCredentialsAuthenticator($this->openId, $this->clientId, $this->clientSecret, $this->scope);
    }
}
