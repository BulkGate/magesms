<?php
namespace BulkGate\Magesms\Extensions\Hook\Channel;

use BulkGate\Magesms\Extensions;

/**
 * Class Sms
 * @package BulkGate\Magesms\Extensions\Hook\Channel
 */
class Sms extends Extensions\Strict implements ChannelInterface
{
    /** @var bool */
    private $active = false;

    /** @var string */
    private $template = '';

    /** @var bool */
    private $unicode = false;

    /** @var bool */
    private $flash = false;

    /** @var string */
    private $senderType = "gSystem";

    /** @var string */
    private $senderValue = "";

    /** @var bool */
    private $customer = false;

    /** @var array */
    private $admins = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            try {
                $this->{$key} = $value;
            } catch (Extensions\Exceptions\StrictException $e) {
                return;
            }
        }
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function toArray()
    {
        return [
            'active'         => (bool) $this->active,
            'template'       => (string) $this->template,
            'unicode'        => (bool) $this->unicode,
            'flash'          => (bool) $this->flash,
            'senderType'     => (string) $this->senderType,
            'senderValue'    => (string) $this->senderValue,
            'customer'       => (bool) $this->customer,
            'admins'         => (array) $this->admins
        ];
    }
}
