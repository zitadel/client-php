<?php
/**
 * BetaFeatureServiceSource
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
 * BetaFeatureServiceSource Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class BetaFeatureServiceSource
{
    /**
     * Possible values of this enum
     */
    public const SOURCE_UNSPECIFIED = 'SOURCE_UNSPECIFIED';

    public const SOURCE_SYSTEM = 'SOURCE_SYSTEM';

    public const SOURCE_INSTANCE = 'SOURCE_INSTANCE';

    public const SOURCE_ORGANIZATION = 'SOURCE_ORGANIZATION';

    public const SOURCE_PROJECT = 'SOURCE_PROJECT';

    public const SOURCE_APP = 'SOURCE_APP';

    public const SOURCE_USER = 'SOURCE_USER';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SOURCE_UNSPECIFIED,
            self::SOURCE_SYSTEM,
            self::SOURCE_INSTANCE,
            self::SOURCE_ORGANIZATION,
            self::SOURCE_PROJECT,
            self::SOURCE_APP,
            self::SOURCE_USER
        ];
    }
}


