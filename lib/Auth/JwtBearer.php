<?php

namespace Zitadel\Client\Auth;

use League\OAuth2\Client\Grant\AbstractGrant;

/**
 * Represents a JWT Bearer grant.
 *
 * @link https://tools.ietf.org/html/rfc7523
 */
class JwtBearer extends AbstractGrant
{
  /**
   * Get the grant's name.
   *
   * @return string
   */
  protected function getName(): string
  {
    return 'urn:ietf:params:oauth:grant-type:jwt-bearer';
  }

  /**
   * Get required parameters for this grant type.
   *
   * @return array
   */
  protected function getRequiredRequestParameters(): array
  {
    return ['assertion'];
  }
}
