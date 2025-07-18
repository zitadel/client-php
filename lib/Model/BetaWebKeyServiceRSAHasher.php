<?php
/**
 * BetaWebKeyServiceRSAHasher
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
 * BetaWebKeyServiceRSAHasher Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class BetaWebKeyServiceRSAHasher
{
    /**
     * Possible values of this enum
     */
    public const RSA_HASHER_UNSPECIFIED = 'RSA_HASHER_UNSPECIFIED';

    public const RSA_HASHER_SHA256 = 'RSA_HASHER_SHA256';

    public const RSA_HASHER_SHA384 = 'RSA_HASHER_SHA384';

    public const RSA_HASHER_SHA512 = 'RSA_HASHER_SHA512';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::RSA_HASHER_UNSPECIFIED,
            self::RSA_HASHER_SHA256,
            self::RSA_HASHER_SHA384,
            self::RSA_HASHER_SHA512
        ];
    }
}


