<?php
namespace BulkGate\Magesms\Extensions;

use stdClass;

/**
 * Class Buffer
 * @package BulkGate\Magesms\Extensions
 */
class Buffer extends stdClass
{
    /** @var array */
    private $buffer = [];

    /** @var mixed */
    private $default_value;

    public function __construct(array $array = [], $default_value = null)
    {
        $this->buffer = $array;
        $this->default_value = $default_value;
    }

    public function __get($name)
    {
        if (isset($this->buffer[$name])) {
            return $this->buffer[$name];
        }
        return $this->default_value;
    }

    public function __set($name, $value)
    {
        $this->buffer[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->buffer[$name]);
    }

    public function toArray()
    {
        return $this->buffer;
    }
}
