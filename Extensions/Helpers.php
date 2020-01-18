<?php
namespace BulkGate\Magesms\Extensions;

use DateTime;

/**
 * Class Helpers
 * @package BulkGate\Magesms\Extensions
 */
class Helpers extends Strict
{
    public static function outOfStockCheck(Settings $settings, $product_id)
    {
        $result = false;

        $list = $settings->load('static:out_of_stock', false);

        $list = $list !== false ?  static::unserialize($list) : [];

        if (is_array($list)) {
            foreach ($list as $key => $time) {
                if ($time < time()) {
                    unset($list[(string)$key]);
                }
            }
        } else {
            $list = [];
        }
        if (!isset($list[(string)$product_id])) {
            $list[(string)$product_id] = (new DateTime('now + 1 day'))->getTimestamp();
            $result = true;
        }

        $settings->set('static:out_of_stock', static::serialize($list));

        return $result;
    }

    public static function serialize($data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (class_exists(\Magento\Framework\Serialize\Serializer\Serialize::class)) {
            $serializer = $objectManager->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
            return $serializer->serialize($data);
        }
        return \Zend\Serializer\Serializer::serialize($data);
    }

    public static function unserialize($data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (class_exists(\Magento\Framework\Serialize\Serializer\Serialize::class)) {
            $serializer = $objectManager->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
            return $serializer->unserialize($data);
        }
        return \Zend\Serializer\Serializer::unserialize($data);
    }
}
