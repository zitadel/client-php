<?php

namespace Zitadel\Client\Auth;

use Exception;

/**
 * Builder for ClientCredentialsAuthenticator.
 *
 * Extends the base OAuthAuthenticatorBuilder to provide a fluent API for constructing
 * a ClientCredentialsAuthenticator instance.
 */
final class ClientCredentialsAuthenticatorBuilder extends OAuthAuthenticatorBuilder
{
    /**
     * Constructs the builder with the required parameters.
     *
     * @param string $host The base URL for API endpoints.
     * @param string $clientId The OAuth2 client identifier.
     * @param string $clientSecret The OAuth2 client secret.
     * @throws Exception
     */
    public function __construct(string $host, private string $clientId, private string $clientSecret)
    {
        parent::__construct($host);
    }

    /**
     * Builds and returns a new ClientCredentialsAuthenticator instance.
     *
     * @return ClientCredentialsAuthenticator
     */
    public function build(): ClientCredentialsAuthenticator
    {
        return new ClientCredentialsAuthenticator(
            $this->hostName,
            $this->clientId,
            $this->clientSecret,
            $this->authScopes
        );
    }
}
