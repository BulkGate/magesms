<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Class Iterator
 * @package BulkGate\Magesms\Extensions
 */
class Iterator extends Strict implements \Iterator
{
    /** @var array */
    protected $array = [];

    /** @var int */
    private $position = 0;

    public function __construct(array $rows)
    {
        $this->array = $rows;
        $this->position = 0;
    }

    public function get($key)
    {
        return isset($this->array[$key]) ? $this->array[$key] : null;
    }

    public function set($key, $value)
    {
        return $this->array[$key] = $value;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->array);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->array);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->array);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->array);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($this->array) !== null;
    }

    public function count()
    {
        return count($this->array);
    }

    public function add($value)
    {
        $this->array[] = $value;
    }
}
