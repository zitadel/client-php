<?php
/**
 * BetaUserServiceHumanProfile
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

use \ArrayAccess;
use \Zitadel\Client\ObjectSerializer;

/**
 * BetaUserServiceHumanProfile Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class BetaUserServiceHumanProfile implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'BetaUserServiceHumanProfile';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'givenName' => 'string',
        'familyName' => 'string',
        'nickName' => 'string',
        'displayName' => 'string',
        'preferredLanguage' => 'string',
        'gender' => '\Zitadel\Client\Model\BetaUserServiceGender',
        'avatarUrl' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'givenName' => null,
        'familyName' => null,
        'nickName' => null,
        'displayName' => null,
        'preferredLanguage' => null,
        'gender' => null,
        'avatarUrl' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'givenName' => false,
        'familyName' => false,
        'nickName' => true,
        'displayName' => true,
        'preferredLanguage' => true,
        'gender' => false,
        'avatarUrl' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'givenName' => 'givenName',
        'familyName' => 'familyName',
        'nickName' => 'nickName',
        'displayName' => 'displayName',
        'preferredLanguage' => 'preferredLanguage',
        'gender' => 'gender',
        'avatarUrl' => 'avatarUrl'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'givenName' => 'setGivenName',
        'familyName' => 'setFamilyName',
        'nickName' => 'setNickName',
        'displayName' => 'setDisplayName',
        'preferredLanguage' => 'setPreferredLanguage',
        'gender' => 'setGender',
        'avatarUrl' => 'setAvatarUrl'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'givenName' => 'getGivenName',
        'familyName' => 'getFamilyName',
        'nickName' => 'getNickName',
        'displayName' => 'getDisplayName',
        'preferredLanguage' => 'getPreferredLanguage',
        'gender' => 'getGender',
        'avatarUrl' => 'getAvatarUrl'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[]|null $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(?array $data = null)
    {
        $this->setIfExists('givenName', $data ?? [], null);
        $this->setIfExists('familyName', $data ?? [], null);
        $this->setIfExists('nickName', $data ?? [], null);
        $this->setIfExists('displayName', $data ?? [], null);
        $this->setIfExists('preferredLanguage', $data ?? [], null);
        $this->setIfExists('gender', $data ?? [], null);
        $this->setIfExists('avatarUrl', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets givenName
     *
     * @return string|null
     */
    public function getGivenName()
    {
        return $this->container['givenName'];
    }

    /**
     * Sets givenName
     *
     * @param string|null $givenName givenName
     *
     * @return self
     */
    public function setGivenName($givenName)
    {
        if (is_null($givenName)) {
            throw new \InvalidArgumentException('non-nullable givenName cannot be null');
        }
        $this->container['givenName'] = $givenName;

        return $this;
    }

    /**
     * Gets familyName
     *
     * @return string|null
     */
    public function getFamilyName()
    {
        return $this->container['familyName'];
    }

    /**
     * Sets familyName
     *
     * @param string|null $familyName familyName
     *
     * @return self
     */
    public function setFamilyName($familyName)
    {
        if (is_null($familyName)) {
            throw new \InvalidArgumentException('non-nullable familyName cannot be null');
        }
        $this->container['familyName'] = $familyName;

        return $this;
    }

    /**
     * Gets nickName
     *
     * @return string|null
     */
    public function getNickName()
    {
        return $this->container['nickName'];
    }

    /**
     * Sets nickName
     *
     * @param string|null $nickName nickName
     *
     * @return self
     */
    public function setNickName($nickName)
    {
        if (is_null($nickName)) {
            array_push($this->openAPINullablesSetToNull, 'nickName');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('nickName', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['nickName'] = $nickName;

        return $this;
    }

    /**
     * Gets displayName
     *
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->container['displayName'];
    }

    /**
     * Sets displayName
     *
     * @param string|null $displayName displayName
     *
     * @return self
     */
    public function setDisplayName($displayName)
    {
        if (is_null($displayName)) {
            array_push($this->openAPINullablesSetToNull, 'displayName');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('displayName', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['displayName'] = $displayName;

        return $this;
    }

    /**
     * Gets preferredLanguage
     *
     * @return string|null
     */
    public function getPreferredLanguage()
    {
        return $this->container['preferredLanguage'];
    }

    /**
     * Sets preferredLanguage
     *
     * @param string|null $preferredLanguage preferredLanguage
     *
     * @return self
     */
    public function setPreferredLanguage($preferredLanguage)
    {
        if (is_null($preferredLanguage)) {
            array_push($this->openAPINullablesSetToNull, 'preferredLanguage');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('preferredLanguage', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['preferredLanguage'] = $preferredLanguage;

        return $this;
    }

    /**
     * Gets gender
     *
     * @return \Zitadel\Client\Model\BetaUserServiceGender|null
     */
    public function getGender()
    {
        return $this->container['gender'];
    }

    /**
     * Sets gender
     *
     * @param \Zitadel\Client\Model\BetaUserServiceGender|null $gender gender
     *
     * @return self
     */
    public function setGender($gender)
    {
        if (is_null($gender)) {
            throw new \InvalidArgumentException('non-nullable gender cannot be null');
        }
        $this->container['gender'] = $gender;

        return $this;
    }

    /**
     * Gets avatarUrl
     *
     * @return string|null
     */
    public function getAvatarUrl()
    {
        return $this->container['avatarUrl'];
    }

    /**
     * Sets avatarUrl
     *
     * @param string|null $avatarUrl avatarUrl
     *
     * @return self
     */
    public function setAvatarUrl($avatarUrl)
    {
        if (is_null($avatarUrl)) {
            throw new \InvalidArgumentException('non-nullable avatarUrl cannot be null');
        }
        $this->container['avatarUrl'] = $avatarUrl;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


