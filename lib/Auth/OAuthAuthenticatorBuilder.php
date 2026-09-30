<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use InvalidArgumentException;

/**
 * Abstract builder for OAuth authenticators.
 *
 * Holds the OpenID discovery helper for the host and the requested scopes.
 */
abstract class OAuthAuthenticatorBuilder
{
    /**
     * The default scopes requested when none are configured.
     */
    public const string DEFAULT_SCOPE = 'openid urn:zitadel:iam:org:project:id:zitadel:aud';

    protected OpenId $openId;

    protected string $scope = self::DEFAULT_SCOPE;

    /**
     * @param string $host The base URL for the OAuth provider.
     * @throws InvalidArgumentException If the host is not a valid http or https URL.
     */
    public function __construct(string $host)
    {
        $this->openId = new OpenId($host);
    }

    /**
     * Overrides the default scopes. Duplicates are dropped; order is kept.
     *
     * @param string ...$authScopes The scopes for the token request.
     * @throws InvalidArgumentException If no scope is given, or a scope is
     *                                  empty or contains whitespace.
     */
    public function scopes(string ...$authScopes): static
    {
        if ($authScopes === []) {
            throw new InvalidArgumentException('At least one scope is required.');
        }
        foreach ($authScopes as $authScope) {
            if ($authScope === '' || preg_match('/\s/', $authScope) === 1) {
                throw new InvalidArgumentException(
                    "Scope must be a non-empty string without whitespace: '$authScope'"
                );
            }
        }
        $this->scope = implode(' ', array_values(array_unique($authScopes)));
        return $this;
    }
}
