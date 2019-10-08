<?php
namespace BulkGate\Magesms\Extensions\Hook;

use BulkGate\Magesms\Extensions;

/**
 * Class Settings
 * @package BulkGate\Magesms\Extensions\Hook
 */
class Settings extends Extensions\Iterator
{
    public function __construct(array $data)
    {
        $settings = [];

        foreach ($data as $type => $channel) {
            switch ($type) {
                case 'sms':
                    $settings[$type] = new Extensions\Hook\Channel\Sms((array)$channel);
                    break;
                default:
                    $settings[$type] = new Extensions\Hook\Channel\DefaultChannel((array)$channel);
                    break;
            }
        }

        parent::__construct($settings);
    }

    public function toArray()
    {
        $output = [];

        /** @var Extensions\Hook\Channel\ChannelInterface $item */
        foreach ($this->array as $key => $item) {
            if ($item->isActive()) {
                $output[$key] = $item->toArray();
            }
        }
        return $output;
    }
}
