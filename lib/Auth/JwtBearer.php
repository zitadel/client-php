<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

/**
 * Constants for the RFC 7523 JWT Bearer grant.
 *
 * Previously this extended league/oauth2-client's AbstractGrant to register
 * the grant with that library's grant factory. The token exchange now goes
 * directly through the SDK transport in {@see OAuthAuthenticator}, so the
 * league dependency has been dropped and this class is retained only to
 * expose the grant identifiers used by {@see WebTokenAuthenticator}.
 *
 * @link https://tools.ietf.org/html/rfc7523
 */
final class JwtBearer
{
    /** The OAuth2 grant_type value sent on the wire. */
    public const string GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    /** Request parameters required by this grant. */
    public const array REQUIRED_PARAMETERS = ['assertion'];
}
