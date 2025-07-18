<?php
/**
 * IdentityProviderServiceIDPType
 *
 * PHP version 8.1
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Zitadel SDK
 *
 * The Zitadel SDK is a convenience wrapper around the Zitadel APIs to assist you in integrating with your Zitadel environment. This SDK enables you to handle resources, settings, and configurations within the Zitadel platform.
 *
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.13.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Zitadel\Client\Model;
use \Zitadel\Client\ObjectSerializer;

/**
 * IdentityProviderServiceIDPType Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class IdentityProviderServiceIDPType
{
    /**
     * Possible values of this enum
     */
    public const IDP_TYPE_UNSPECIFIED = 'IDP_TYPE_UNSPECIFIED';

    public const IDP_TYPE_OIDC = 'IDP_TYPE_OIDC';

    public const IDP_TYPE_JWT = 'IDP_TYPE_JWT';

    public const IDP_TYPE_LDAP = 'IDP_TYPE_LDAP';

    public const IDP_TYPE_OAUTH = 'IDP_TYPE_OAUTH';

    public const IDP_TYPE_AZURE_AD = 'IDP_TYPE_AZURE_AD';

    public const IDP_TYPE_GITHUB = 'IDP_TYPE_GITHUB';

    public const IDP_TYPE_GITHUB_ES = 'IDP_TYPE_GITHUB_ES';

    public const IDP_TYPE_GITLAB = 'IDP_TYPE_GITLAB';

    public const IDP_TYPE_GITLAB_SELF_HOSTED = 'IDP_TYPE_GITLAB_SELF_HOSTED';

    public const IDP_TYPE_GOOGLE = 'IDP_TYPE_GOOGLE';

    public const IDP_TYPE_APPLE = 'IDP_TYPE_APPLE';

    public const IDP_TYPE_SAML = 'IDP_TYPE_SAML';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::IDP_TYPE_UNSPECIFIED,
            self::IDP_TYPE_OIDC,
            self::IDP_TYPE_JWT,
            self::IDP_TYPE_LDAP,
            self::IDP_TYPE_OAUTH,
            self::IDP_TYPE_AZURE_AD,
            self::IDP_TYPE_GITHUB,
            self::IDP_TYPE_GITHUB_ES,
            self::IDP_TYPE_GITLAB,
            self::IDP_TYPE_GITLAB_SELF_HOSTED,
            self::IDP_TYPE_GOOGLE,
            self::IDP_TYPE_APPLE,
            self::IDP_TYPE_SAML
        ];
    }
}


