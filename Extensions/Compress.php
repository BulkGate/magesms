<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Class Compress
 * @package BulkGate\Magesms\Extensions
 */
class Compress
{
    public static function compress($data, $encoding_mode = 9)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Serialize\Serializer\Serialize $serialize */
        $serialize = $objectManager->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
        return base64_encode(gzencode($serialize->serialize($data), $encoding_mode));
    }

    public static function decompress($data)
    {
        if ($data) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Serialize\Serializer\Serialize $serialize */
            $serialize = $objectManager->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
            return $serialize->unserialize(gzinflate(substr(base64_decode($data), 10, -8)));
        }
        return false;
    }
}
