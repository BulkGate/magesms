<?php
namespace BulkGate\Magesms\Extensions\IO;

use BulkGate\Magesms\Extensions;
use stdClass;

/**
 * Class Response
 * @package BulkGate\Magesms\Extensions\IO
 */
class Response extends stdClass
{
    public function __construct($data, $content_type = null)
    {
        if (is_string($data)) {
            if ($content_type === 'application/json') {
                try {
                    $result = Extensions\Json::decode($data, Extensions\Json::FORCE_ARRAY);

                    if (is_array($result)) {
                        $this->load((array)$result);
                    }
                } catch (Extensions\Exceptions\JsonException $e) {
                    throw new Extensions\IO\Exceptions\InvalidResultException('Json parse error: ' . $data);
                }
            } elseif ($content_type === 'application/zip') {
                $result = Extensions\Json::decode(Extensions\Compress::decompress($data));

                if (is_array($result) || $result instanceof stdClass) {
                    $this->load((array)$result);
                }
            } else {
                throw new Extensions\IO\Exceptions\InvalidResultException('Invalid content type' . $data);
            }
        } elseif (is_array($data)) {
            $this->load($data);
        } else {
            throw new Extensions\IO\Exceptions\InvalidResultException('Input not string (JSON)');
        }
    }

    public function load(array $array)
    {
        if (isset($array['signal']) && $array['signal'] === 'authenticate') {
            throw new Extensions\IO\Exceptions\AuthenticateException;
        } else {
            foreach ($array as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    public function get($key)
    {
        $path = Key::decode($key);

        return array_reduce($path, function ($prev, $now) {
            if ($now === Key::DEFAULT_VARIABLE) {
                return $prev;
            } else {
                if ($prev) {
                    if (is_array($prev)) {
                        return isset($prev[$now]) ? $prev[$now] : null;
                    } else {
                        return isset($prev->$now) ? $prev->$now : null;
                    }
                } else {
                    return null;
                }
            }
        }, $this->data);
    }

    public function remove($key)
    {
        if (isset($this->data)) {
            list($reducer, $container, $variable) = Key::decode($key);

            if (isset(
                $this->data->{$reducer},
                $this->data->{$reducer}->{$container},
                $this->data->{$reducer}->{$container}->{$variable}
            )
            ) {
                unset($this->data->{$reducer}->{$container}->{$variable});
            } elseif (isset($this->data->{$reducer}, $this->data->{$reducer}->{$container})) {
                unset($this->data->{$reducer}->{$container});
            } elseif (isset($this->data->{$reducer})) {
                unset($this->data->{$reducer});
            }
        }
    }

    public function set($key, $value)
    {
        if (isset($this->data)) {
            list($reducer, $container, $variable) = Key::decode($key);

            if (!isset($this->data[$reducer])) {
                $this->data[$reducer] = [];
            }

            if (!isset($this->data[$reducer][$container])) {
                $this->data[$reducer][$container] = [];
            }

            $this->data[$reducer][$container][$variable] = $value;
        }
    }
}
