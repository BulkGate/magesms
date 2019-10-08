<?php
namespace BulkGate\Magesms\Extensions\Hook\Channel;

use stdClass;

/**
 * Class DefaultChannel
 * @package BulkGate\Magesms\Extensions\Hook\Channel
 */
class DefaultChannel extends stdClass implements ChannelInterface
{
    /** @var bool */
    public $active = false;

    /** @var string */
    public $template = '';

    /** @var bool */
    public $customer = false;

    /** @var array */
    public $admins = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function toArray()
    {
        return (array) $this;
    }
}
