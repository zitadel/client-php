<?php
/**
 * IdentityProviderServiceIDPState
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
 * IdentityProviderServiceIDPState Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class IdentityProviderServiceIDPState
{
    /**
     * Possible values of this enum
     */
    public const IDP_STATE_UNSPECIFIED = 'IDP_STATE_UNSPECIFIED';

    public const IDP_STATE_ACTIVE = 'IDP_STATE_ACTIVE';

    public const IDP_STATE_INACTIVE = 'IDP_STATE_INACTIVE';

    public const IDP_STATE_REMOVED = 'IDP_STATE_REMOVED';

    public const IDP_STATE_MIGRATED = 'IDP_STATE_MIGRATED';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::IDP_STATE_UNSPECIFIED,
            self::IDP_STATE_ACTIVE,
            self::IDP_STATE_INACTIVE,
            self::IDP_STATE_REMOVED,
            self::IDP_STATE_MIGRATED
        ];
    }
}


