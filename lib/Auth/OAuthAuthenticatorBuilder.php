<?php

namespace Zitadel\Client\Auth;

use Exception;
use Zitadel\Client\TransportOptions;

/**
 * Base builder for OAuth authenticators.
 *
 * Provides fluent methods to override the default token endpoint and scopes.
 * Subclasses extend this builder to construct specific OAuthAuthenticator instances.
 */
abstract class OAuthAuthenticatorBuilder
{
    protected OpenId $hostName;
    protected string $authScopes = 'openid urn:zitadel:iam:org:project:id:zitadel:aud';
    protected TransportOptions $transportOptions;

    /**
     * Constructs the builder with the required host.
     *
     * @param string $hostName
     * @param TransportOptions|null $transportOptions Optional transport options for HTTP connections.
     * @throws Exception
     */
    public function __construct(
        string $hostName,
        ?TransportOptions $transportOptions = null,
    ) {
        $this->transportOptions = $transportOptions ?? TransportOptions::defaults();
        $this->hostName = new OpenId($hostName, $this->transportOptions);
    }

    /**
     * Overrides the default scopes.
     *
     * @param string[] $authScopes A list of scopes for the token request.
     * @return static
     */
    public function scopes(array $authScopes): static
    {
        $this->authScopes = implode(' ', $authScopes);
        return $this;
    }
}
