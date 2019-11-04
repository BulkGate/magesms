<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Class Strict
 * @package BulkGate\Magesms\Extensions
 */
class Strict
{
    /**
     * @param $name
     * @return mixed
     * @throws Exceptions\StrictException
     */
    public function __get($name)
    {
        $class = get_class($this);

        if (property_exists($class, $name)) {
            return $this->$name;
        }

        throw new Exceptions\StrictException("Cannot read an undeclared property $class::\$$name.");
    }

    /**
     * @param $name
     * @param $value
     * @throws Exceptions\StrictException
     */
    public function __set($name, $value)
    {
        $class = get_class($this);

        if (property_exists($class, $name)) {
            $this->$name = $value;
        } else {
            throw new Exceptions\StrictException("Cannot write an undeclared property $class::\$$name.");
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $class = get_class($this);

        if (property_exists($class, $name)) {
            return isset($this->$name);
        }

        return false;
    }

    /**
     * @param string $name
     * @throws Exceptions\StrictException
     */
    public function __unset($name)
    {
        throw new Exceptions\StrictException('You can\'t unset undeclared property '.__CLASS__.'::$'.$name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @throws Exceptions\StrictException
     */
    public function __call($name, array $arguments)
    {
        throw new Exceptions\StrictException('You can\'t call undeclared method '.__CLASS__.'::$'.$name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @throws Exceptions\StrictException
     */
    public static function __callStatic($name, array $arguments)
    {
        throw new Exceptions\StrictException('You can\'t statically call undeclared method '.__CLASS__.'::$'.$name);
    }
}
