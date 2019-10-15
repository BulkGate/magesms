<?php
namespace BulkGate\Magesms\Extensions\Hook;

use BulkGate\Magesms\Extensions\Strict;

/**
 * Class Variables
 * @package BulkGate\Magesms\Extensions\Hook
 */
class Variables extends Strict
{
    /** @var array */
    private $variables;

    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    public function set($key, $value, $alternative = '', $rewrite = true)
    {
        if (!isset($this->variables[$key]) || $rewrite) {
            if (is_scalar($value) && strlen(trim((string)$value)) > 0) {
                $this->variables[$key] = $value;
            }

            if (!isset($this->variables[$key])) {
                if (is_scalar($alternative) && strlen(trim((string)$alternative)) > 0) {
                    $this->variables[$key] = (string)$alternative;
                } else {
                    $this->variables[$key] = '';
                }
            }
        }

        return $this;
    }

    public function get($key, $default = false)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }
        return $default;
    }

    public function toArray()
    {
        return $this->variables;
    }

    public function __toString()
    {
        $output = '$php = array('.PHP_EOL;

        foreach ($this->variables as $key => $variable) {
            $output .= "\t" . '\'' . $key . '\' => \'' . $variable . '\',' . PHP_EOL;
        }

        $output .= ');';

        return $output;
    }
}
