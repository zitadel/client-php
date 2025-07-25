<?php
/**
 * SessionServiceUserVerificationRequirement
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
 * SessionServiceUserVerificationRequirement Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class SessionServiceUserVerificationRequirement
{
    /**
     * Possible values of this enum
     */
    public const USER_VERIFICATION_REQUIREMENT_UNSPECIFIED = 'USER_VERIFICATION_REQUIREMENT_UNSPECIFIED';

    public const USER_VERIFICATION_REQUIREMENT_REQUIRED = 'USER_VERIFICATION_REQUIREMENT_REQUIRED';

    public const USER_VERIFICATION_REQUIREMENT_PREFERRED = 'USER_VERIFICATION_REQUIREMENT_PREFERRED';

    public const USER_VERIFICATION_REQUIREMENT_DISCOURAGED = 'USER_VERIFICATION_REQUIREMENT_DISCOURAGED';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::USER_VERIFICATION_REQUIREMENT_UNSPECIFIED,
            self::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            self::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            self::USER_VERIFICATION_REQUIREMENT_DISCOURAGED
        ];
    }
}


