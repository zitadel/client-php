<?php
/**
 * BetaSettingsServiceThemeMode
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
 * BetaSettingsServiceThemeMode Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class BetaSettingsServiceThemeMode
{
    /**
     * Possible values of this enum
     */
    public const THEME_MODE_UNSPECIFIED = 'THEME_MODE_UNSPECIFIED';

    public const THEME_MODE_AUTO = 'THEME_MODE_AUTO';

    public const THEME_MODE_LIGHT = 'THEME_MODE_LIGHT';

    public const THEME_MODE_DARK = 'THEME_MODE_DARK';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::THEME_MODE_UNSPECIFIED,
            self::THEME_MODE_AUTO,
            self::THEME_MODE_LIGHT,
            self::THEME_MODE_DARK
        ];
    }
}


