<?php

namespace Zitadel\Client\Test;

use ArrayAccess;
use Exception;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\Model\ModelInterface;
use Zitadel\Client\ObjectSerializer;
use Stringable;

class ObjectSerializerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRoundTrip()
    {
        $json = file_get_contents(__DIR__ . '/resources/serde.json');
        $model = ObjectSerializer::deserialize($json, SerdeModel::class);
        $data = ObjectSerializer::sanitizeForSerialization($model);
        $this->assertJsonStringEqualsJsonString(
            $json,
            json_encode($data)
        );
    }
}

class SerdeModel implements ModelInterface, ArrayAccess, JsonSerializable, Stringable
{
    protected static $openAPITypes = [
        'some_string' => 'string',
        'some_binary' => 'string',
        'some_byte' => 'string',
        'some_date' => '\\DateTime',
        'some_date_time' => '\\DateTime',
        'some_password' => 'string',
        'some_email' => 'string',
        'some_hostname' => 'string',
        'some_ipv4' => 'string',
        'some_ipv6' => 'string',
        'some_uri' => 'string',
        'some_uri_reference' => 'string',
        'some_uri_template' => 'string',
        'some_json_pointer' => 'string',
        'some_relative_json_pointer' => 'string',
        'some_regex' => 'string',
        'some_number' => 'float',
        'some_float' => 'float',
        'some_double' => 'float',
        'some_integer' => 'int',
        'some_int32' => 'int',
        'some_int64' => 'int',
        'some_boolean' => 'bool',
        'some_array' => 'string[]',
        'some_object' => 'mixed',
        'some_nested_object' => 'mixed',
        'some_array_of_objects' => 'mixed[]',
        'some_nullable_field' => 'mixed'
    ];
    protected static $openAPIFormats = [
        'some_binary' => 'binary',
        'some_byte' => 'byte',
        'some_date' => 'date',
        'some_date_time' => 'date-time',
        'some_email' => 'email',
        'some_hostname' => 'hostname',
        'some_ipv4' => 'ipv4',
        'some_ipv6' => 'ipv6',
        'some_uri' => 'uri',
        'some_uri_reference' => 'uri-reference',
        'some_uri_template' => 'uri-template',
        'some_json_pointer' => 'json-pointer',
        'some_relative_json_pointer' => 'relative-json-pointer',
        'some_regex' => 'regex',
        'some_float' => 'float',
        'some_double' => 'double',
        'some_int32' => 'int32',
        'some_int64' => 'int64'
    ];
    protected static array $openAPINullables = ['some_nullable_field' => true];
    protected $openAPINullablesSetToNull = [];
    protected static $attributeMap = [];
    protected static $setters = [];
    protected static $getters = [];
    protected $container = [];

    public function __construct(?array $data = null)
    {
        $data ??= [];
        if (empty(self::$attributeMap)) {
            foreach (self::$openAPITypes as $p => $t) {
                self::$attributeMap[$p] = $p;
                self::$setters[$p] = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $p)));
                self::$getters[$p] = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $p)));
            }
        }
        foreach (self::$attributeMap as $prop => $key) {
            if (array_key_exists($key, $data) && $data[$key] === null && self::isNullable($prop)) {
                $this->openAPINullablesSetToNull[] = $prop;
            }
            $this->container[$prop] = $data[$key] ?? null;
        }
    }

    public static function openAPITypes(): array
    {
        return self::$openAPITypes;
    }

    public static function openAPIFormats(): array
    {
        return self::$openAPIFormats;
    }

    public static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    public static function attributeMap(): array
    {
        return self::$attributeMap;
    }

    public static function setters(): array
    {
        return self::$setters;
    }

    public static function getters(): array
    {
        return self::$getters;
    }

    public function getModelName(): string
    {
        return 'SerdeModel';
    }

    public function listInvalidProperties(): array
    {
        return [];
    }

    public function valid(): bool
    {
        return count($this->listInvalidProperties()) === 0;
    }

    public static function isNullable(string $prop): bool
    {
        return self::$openAPINullables[$prop] ?? false;
    }

    public function isNullableSetToNull(string $prop): bool
    {
        return in_array($prop, $this->openAPINullablesSetToNull, true);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    public function jsonSerialize()
    {
        return ObjectSerializer::sanitizeForSerialization($this);
    }

    public function __toString(): string
    {
        return (string)json_encode(ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
    }

    public function toHeaderValue(): string
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }

    public function __call($name, $args)
    {
        if (str_starts_with($name, 'get')) {
            $prop = lcfirst(substr($name, 3));
            return $this->container[$prop] ?? null;
        }
        if (str_starts_with($name, 'set')) {
            $prop = lcfirst(substr($name, 3));
            $this->container[$prop] = $args[0] ?? null;
            return $this;
        }
    }
}
