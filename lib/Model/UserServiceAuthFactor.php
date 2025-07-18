<?php
/**
 * UserServiceAuthFactor
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
 * UserServiceAuthFactor Class Doc Comment
 *
 * @category Class
 * @package  Zitadel\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class UserServiceAuthFactor implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'UserServiceAuthFactor';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'state' => '\Zitadel\Client\Model\UserServiceAuthFactorState',
        'otp' => 'object',
        'otpEmail' => 'object',
        'otpSms' => 'object',
        'u2f' => '\Zitadel\Client\Model\UserServiceAuthFactorU2F'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'state' => null,
        'otp' => null,
        'otpEmail' => null,
        'otpSms' => null,
        'u2f' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'state' => false,
        'otp' => false,
        'otpEmail' => false,
        'otpSms' => false,
        'u2f' => false
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
        'state' => 'state',
        'otp' => 'otp',
        'otpEmail' => 'otpEmail',
        'otpSms' => 'otpSms',
        'u2f' => 'u2f'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'state' => 'setState',
        'otp' => 'setOtp',
        'otpEmail' => 'setOtpEmail',
        'otpSms' => 'setOtpSms',
        'u2f' => 'setU2f'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'state' => 'getState',
        'otp' => 'getOtp',
        'otpEmail' => 'getOtpEmail',
        'otpSms' => 'getOtpSms',
        'u2f' => 'getU2f'
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
        $this->setIfExists('state', $data ?? [], null);
        $this->setIfExists('otp', $data ?? [], null);
        $this->setIfExists('otpEmail', $data ?? [], null);
        $this->setIfExists('otpSms', $data ?? [], null);
        $this->setIfExists('u2f', $data ?? [], null);
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
     * Gets state
     *
     * @return \Zitadel\Client\Model\UserServiceAuthFactorState|null
     */
    public function getState()
    {
        return $this->container['state'];
    }

    /**
     * Sets state
     *
     * @param \Zitadel\Client\Model\UserServiceAuthFactorState|null $state state
     *
     * @return self
     */
    public function setState($state)
    {
        if (is_null($state)) {
            throw new \InvalidArgumentException('non-nullable state cannot be null');
        }
        $this->container['state'] = $state;

        return $this;
    }

    /**
     * Gets otp
     *
     * @return object|null
     */
    public function getOtp()
    {
        return $this->container['otp'];
    }

    /**
     * Sets otp
     *
     * @param object|null $otp otp
     *
     * @return self
     */
    public function setOtp($otp)
    {
        if (is_null($otp)) {
            throw new \InvalidArgumentException('non-nullable otp cannot be null');
        }
        $this->container['otp'] = $otp;

        return $this;
    }

    /**
     * Gets otpEmail
     *
     * @return object|null
     */
    public function getOtpEmail()
    {
        return $this->container['otpEmail'];
    }

    /**
     * Sets otpEmail
     *
     * @param object|null $otpEmail otpEmail
     *
     * @return self
     */
    public function setOtpEmail($otpEmail)
    {
        if (is_null($otpEmail)) {
            throw new \InvalidArgumentException('non-nullable otpEmail cannot be null');
        }
        $this->container['otpEmail'] = $otpEmail;

        return $this;
    }

    /**
     * Gets otpSms
     *
     * @return object|null
     */
    public function getOtpSms()
    {
        return $this->container['otpSms'];
    }

    /**
     * Sets otpSms
     *
     * @param object|null $otpSms otpSms
     *
     * @return self
     */
    public function setOtpSms($otpSms)
    {
        if (is_null($otpSms)) {
            throw new \InvalidArgumentException('non-nullable otpSms cannot be null');
        }
        $this->container['otpSms'] = $otpSms;

        return $this;
    }

    /**
     * Gets u2f
     *
     * @return \Zitadel\Client\Model\UserServiceAuthFactorU2F|null
     */
    public function getU2f()
    {
        return $this->container['u2f'];
    }

    /**
     * Sets u2f
     *
     * @param \Zitadel\Client\Model\UserServiceAuthFactorU2F|null $u2f u2f
     *
     * @return self
     */
    public function setU2f($u2f)
    {
        if (is_null($u2f)) {
            throw new \InvalidArgumentException('non-nullable u2f cannot be null');
        }
        $this->container['u2f'] = $u2f;

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


