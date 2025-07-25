<?php
/**
 * IdentityProviderServiceLDAPAttributes
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
 * IdentityProviderServiceLDAPAttributes Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class IdentityProviderServiceLDAPAttributes implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'IdentityProviderServiceLDAPAttributes';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'idAttribute' => 'string',
        'firstNameAttribute' => 'string',
        'lastNameAttribute' => 'string',
        'displayNameAttribute' => 'string',
        'nickNameAttribute' => 'string',
        'preferredUsernameAttribute' => 'string',
        'emailAttribute' => 'string',
        'emailVerifiedAttribute' => 'string',
        'phoneAttribute' => 'string',
        'phoneVerifiedAttribute' => 'string',
        'preferredLanguageAttribute' => 'string',
        'avatarUrlAttribute' => 'string',
        'profileAttribute' => 'string',
        'rootCa' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'idAttribute' => null,
        'firstNameAttribute' => null,
        'lastNameAttribute' => null,
        'displayNameAttribute' => null,
        'nickNameAttribute' => null,
        'preferredUsernameAttribute' => null,
        'emailAttribute' => null,
        'emailVerifiedAttribute' => null,
        'phoneAttribute' => null,
        'phoneVerifiedAttribute' => null,
        'preferredLanguageAttribute' => null,
        'avatarUrlAttribute' => null,
        'profileAttribute' => null,
        'rootCa' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'idAttribute' => false,
        'firstNameAttribute' => false,
        'lastNameAttribute' => false,
        'displayNameAttribute' => false,
        'nickNameAttribute' => false,
        'preferredUsernameAttribute' => false,
        'emailAttribute' => false,
        'emailVerifiedAttribute' => false,
        'phoneAttribute' => false,
        'phoneVerifiedAttribute' => false,
        'preferredLanguageAttribute' => false,
        'avatarUrlAttribute' => false,
        'profileAttribute' => false,
        'rootCa' => false
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
        'idAttribute' => 'idAttribute',
        'firstNameAttribute' => 'firstNameAttribute',
        'lastNameAttribute' => 'lastNameAttribute',
        'displayNameAttribute' => 'displayNameAttribute',
        'nickNameAttribute' => 'nickNameAttribute',
        'preferredUsernameAttribute' => 'preferredUsernameAttribute',
        'emailAttribute' => 'emailAttribute',
        'emailVerifiedAttribute' => 'emailVerifiedAttribute',
        'phoneAttribute' => 'phoneAttribute',
        'phoneVerifiedAttribute' => 'phoneVerifiedAttribute',
        'preferredLanguageAttribute' => 'preferredLanguageAttribute',
        'avatarUrlAttribute' => 'avatarUrlAttribute',
        'profileAttribute' => 'profileAttribute',
        'rootCa' => 'rootCa'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'idAttribute' => 'setIdAttribute',
        'firstNameAttribute' => 'setFirstNameAttribute',
        'lastNameAttribute' => 'setLastNameAttribute',
        'displayNameAttribute' => 'setDisplayNameAttribute',
        'nickNameAttribute' => 'setNickNameAttribute',
        'preferredUsernameAttribute' => 'setPreferredUsernameAttribute',
        'emailAttribute' => 'setEmailAttribute',
        'emailVerifiedAttribute' => 'setEmailVerifiedAttribute',
        'phoneAttribute' => 'setPhoneAttribute',
        'phoneVerifiedAttribute' => 'setPhoneVerifiedAttribute',
        'preferredLanguageAttribute' => 'setPreferredLanguageAttribute',
        'avatarUrlAttribute' => 'setAvatarUrlAttribute',
        'profileAttribute' => 'setProfileAttribute',
        'rootCa' => 'setRootCa'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'idAttribute' => 'getIdAttribute',
        'firstNameAttribute' => 'getFirstNameAttribute',
        'lastNameAttribute' => 'getLastNameAttribute',
        'displayNameAttribute' => 'getDisplayNameAttribute',
        'nickNameAttribute' => 'getNickNameAttribute',
        'preferredUsernameAttribute' => 'getPreferredUsernameAttribute',
        'emailAttribute' => 'getEmailAttribute',
        'emailVerifiedAttribute' => 'getEmailVerifiedAttribute',
        'phoneAttribute' => 'getPhoneAttribute',
        'phoneVerifiedAttribute' => 'getPhoneVerifiedAttribute',
        'preferredLanguageAttribute' => 'getPreferredLanguageAttribute',
        'avatarUrlAttribute' => 'getAvatarUrlAttribute',
        'profileAttribute' => 'getProfileAttribute',
        'rootCa' => 'getRootCa'
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
        $this->setIfExists('idAttribute', $data ?? [], null);
        $this->setIfExists('firstNameAttribute', $data ?? [], null);
        $this->setIfExists('lastNameAttribute', $data ?? [], null);
        $this->setIfExists('displayNameAttribute', $data ?? [], null);
        $this->setIfExists('nickNameAttribute', $data ?? [], null);
        $this->setIfExists('preferredUsernameAttribute', $data ?? [], null);
        $this->setIfExists('emailAttribute', $data ?? [], null);
        $this->setIfExists('emailVerifiedAttribute', $data ?? [], null);
        $this->setIfExists('phoneAttribute', $data ?? [], null);
        $this->setIfExists('phoneVerifiedAttribute', $data ?? [], null);
        $this->setIfExists('preferredLanguageAttribute', $data ?? [], null);
        $this->setIfExists('avatarUrlAttribute', $data ?? [], null);
        $this->setIfExists('profileAttribute', $data ?? [], null);
        $this->setIfExists('rootCa', $data ?? [], null);
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
     * Gets idAttribute
     *
     * @return string|null
     */
    public function getIdAttribute()
    {
        return $this->container['idAttribute'];
    }

    /**
     * Sets idAttribute
     *
     * @param string|null $idAttribute idAttribute
     *
     * @return self
     */
    public function setIdAttribute($idAttribute)
    {
        if (is_null($idAttribute)) {
            throw new \InvalidArgumentException('non-nullable idAttribute cannot be null');
        }
        $this->container['idAttribute'] = $idAttribute;

        return $this;
    }

    /**
     * Gets firstNameAttribute
     *
     * @return string|null
     */
    public function getFirstNameAttribute()
    {
        return $this->container['firstNameAttribute'];
    }

    /**
     * Sets firstNameAttribute
     *
     * @param string|null $firstNameAttribute firstNameAttribute
     *
     * @return self
     */
    public function setFirstNameAttribute($firstNameAttribute)
    {
        if (is_null($firstNameAttribute)) {
            throw new \InvalidArgumentException('non-nullable firstNameAttribute cannot be null');
        }
        $this->container['firstNameAttribute'] = $firstNameAttribute;

        return $this;
    }

    /**
     * Gets lastNameAttribute
     *
     * @return string|null
     */
    public function getLastNameAttribute()
    {
        return $this->container['lastNameAttribute'];
    }

    /**
     * Sets lastNameAttribute
     *
     * @param string|null $lastNameAttribute lastNameAttribute
     *
     * @return self
     */
    public function setLastNameAttribute($lastNameAttribute)
    {
        if (is_null($lastNameAttribute)) {
            throw new \InvalidArgumentException('non-nullable lastNameAttribute cannot be null');
        }
        $this->container['lastNameAttribute'] = $lastNameAttribute;

        return $this;
    }

    /**
     * Gets displayNameAttribute
     *
     * @return string|null
     */
    public function getDisplayNameAttribute()
    {
        return $this->container['displayNameAttribute'];
    }

    /**
     * Sets displayNameAttribute
     *
     * @param string|null $displayNameAttribute displayNameAttribute
     *
     * @return self
     */
    public function setDisplayNameAttribute($displayNameAttribute)
    {
        if (is_null($displayNameAttribute)) {
            throw new \InvalidArgumentException('non-nullable displayNameAttribute cannot be null');
        }
        $this->container['displayNameAttribute'] = $displayNameAttribute;

        return $this;
    }

    /**
     * Gets nickNameAttribute
     *
     * @return string|null
     */
    public function getNickNameAttribute()
    {
        return $this->container['nickNameAttribute'];
    }

    /**
     * Sets nickNameAttribute
     *
     * @param string|null $nickNameAttribute nickNameAttribute
     *
     * @return self
     */
    public function setNickNameAttribute($nickNameAttribute)
    {
        if (is_null($nickNameAttribute)) {
            throw new \InvalidArgumentException('non-nullable nickNameAttribute cannot be null');
        }
        $this->container['nickNameAttribute'] = $nickNameAttribute;

        return $this;
    }

    /**
     * Gets preferredUsernameAttribute
     *
     * @return string|null
     */
    public function getPreferredUsernameAttribute()
    {
        return $this->container['preferredUsernameAttribute'];
    }

    /**
     * Sets preferredUsernameAttribute
     *
     * @param string|null $preferredUsernameAttribute preferredUsernameAttribute
     *
     * @return self
     */
    public function setPreferredUsernameAttribute($preferredUsernameAttribute)
    {
        if (is_null($preferredUsernameAttribute)) {
            throw new \InvalidArgumentException('non-nullable preferredUsernameAttribute cannot be null');
        }
        $this->container['preferredUsernameAttribute'] = $preferredUsernameAttribute;

        return $this;
    }

    /**
     * Gets emailAttribute
     *
     * @return string|null
     */
    public function getEmailAttribute()
    {
        return $this->container['emailAttribute'];
    }

    /**
     * Sets emailAttribute
     *
     * @param string|null $emailAttribute emailAttribute
     *
     * @return self
     */
    public function setEmailAttribute($emailAttribute)
    {
        if (is_null($emailAttribute)) {
            throw new \InvalidArgumentException('non-nullable emailAttribute cannot be null');
        }
        $this->container['emailAttribute'] = $emailAttribute;

        return $this;
    }

    /**
     * Gets emailVerifiedAttribute
     *
     * @return string|null
     */
    public function getEmailVerifiedAttribute()
    {
        return $this->container['emailVerifiedAttribute'];
    }

    /**
     * Sets emailVerifiedAttribute
     *
     * @param string|null $emailVerifiedAttribute emailVerifiedAttribute
     *
     * @return self
     */
    public function setEmailVerifiedAttribute($emailVerifiedAttribute)
    {
        if (is_null($emailVerifiedAttribute)) {
            throw new \InvalidArgumentException('non-nullable emailVerifiedAttribute cannot be null');
        }
        $this->container['emailVerifiedAttribute'] = $emailVerifiedAttribute;

        return $this;
    }

    /**
     * Gets phoneAttribute
     *
     * @return string|null
     */
    public function getPhoneAttribute()
    {
        return $this->container['phoneAttribute'];
    }

    /**
     * Sets phoneAttribute
     *
     * @param string|null $phoneAttribute phoneAttribute
     *
     * @return self
     */
    public function setPhoneAttribute($phoneAttribute)
    {
        if (is_null($phoneAttribute)) {
            throw new \InvalidArgumentException('non-nullable phoneAttribute cannot be null');
        }
        $this->container['phoneAttribute'] = $phoneAttribute;

        return $this;
    }

    /**
     * Gets phoneVerifiedAttribute
     *
     * @return string|null
     */
    public function getPhoneVerifiedAttribute()
    {
        return $this->container['phoneVerifiedAttribute'];
    }

    /**
     * Sets phoneVerifiedAttribute
     *
     * @param string|null $phoneVerifiedAttribute phoneVerifiedAttribute
     *
     * @return self
     */
    public function setPhoneVerifiedAttribute($phoneVerifiedAttribute)
    {
        if (is_null($phoneVerifiedAttribute)) {
            throw new \InvalidArgumentException('non-nullable phoneVerifiedAttribute cannot be null');
        }
        $this->container['phoneVerifiedAttribute'] = $phoneVerifiedAttribute;

        return $this;
    }

    /**
     * Gets preferredLanguageAttribute
     *
     * @return string|null
     */
    public function getPreferredLanguageAttribute()
    {
        return $this->container['preferredLanguageAttribute'];
    }

    /**
     * Sets preferredLanguageAttribute
     *
     * @param string|null $preferredLanguageAttribute preferredLanguageAttribute
     *
     * @return self
     */
    public function setPreferredLanguageAttribute($preferredLanguageAttribute)
    {
        if (is_null($preferredLanguageAttribute)) {
            throw new \InvalidArgumentException('non-nullable preferredLanguageAttribute cannot be null');
        }
        $this->container['preferredLanguageAttribute'] = $preferredLanguageAttribute;

        return $this;
    }

    /**
     * Gets avatarUrlAttribute
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        return $this->container['avatarUrlAttribute'];
    }

    /**
     * Sets avatarUrlAttribute
     *
     * @param string|null $avatarUrlAttribute avatarUrlAttribute
     *
     * @return self
     */
    public function setAvatarUrlAttribute($avatarUrlAttribute)
    {
        if (is_null($avatarUrlAttribute)) {
            throw new \InvalidArgumentException('non-nullable avatarUrlAttribute cannot be null');
        }
        $this->container['avatarUrlAttribute'] = $avatarUrlAttribute;

        return $this;
    }

    /**
     * Gets profileAttribute
     *
     * @return string|null
     */
    public function getProfileAttribute()
    {
        return $this->container['profileAttribute'];
    }

    /**
     * Sets profileAttribute
     *
     * @param string|null $profileAttribute profileAttribute
     *
     * @return self
     */
    public function setProfileAttribute($profileAttribute)
    {
        if (is_null($profileAttribute)) {
            throw new \InvalidArgumentException('non-nullable profileAttribute cannot be null');
        }
        $this->container['profileAttribute'] = $profileAttribute;

        return $this;
    }

    /**
     * Gets rootCa
     *
     * @return string|null
     */
    public function getRootCa()
    {
        return $this->container['rootCa'];
    }

    /**
     * Sets rootCa
     *
     * @param string|null $rootCa rootCa
     *
     * @return self
     */
    public function setRootCa($rootCa)
    {
        if (is_null($rootCa)) {
            throw new \InvalidArgumentException('non-nullable rootCa cannot be null');
        }
        $this->container['rootCa'] = $rootCa;

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


