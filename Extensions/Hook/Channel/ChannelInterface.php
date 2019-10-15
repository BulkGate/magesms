<?php
namespace BulkGate\Magesms\Extensions\Hook\Channel;

interface ChannelInterface
{
    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return array
     */
    public function toArray();
}
