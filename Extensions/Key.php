<?php
namespace BulkGate\Magesms\Extensions;

use BulkGate\Magesms\Extensions\Exceptions\InvalidKeyException;

/**
 * Class Key
 * @package BulkGate\Magesms\Extensions
 */
class Key
{
    const DEFAULT_SCOPE = 'main';

    public static function decode($key)
    {
        if (preg_match('~^(?<scope>[a-zA-Z0-9_\-;]*):(?<key>[a-zA-Z0-9_\-;]*)$~', $key, $match)) {
            return [$match['scope'] ?: self::DEFAULT_SCOPE, $match['key'] ?: null];
        }
        if (preg_match('~^(?<key>[a-zA-Z0-9_\-;]*)$~', $key, $match)) {
            return [self::DEFAULT_SCOPE, $match['key'] ?: null];
        }
        throw new InvalidKeyException;
    }

    public static function encode($scope, $key, $delimiter = ':')
    {
        return $scope . $delimiter . $key;
    }
}
