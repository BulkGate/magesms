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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serializer = $objectManager->get(\Magento\Framework\Serialize\SerializerInterface::class);
        $result = false;

        $list = $settings->load('static:out_of_stock', false);

        $list = $list !== false ?  $serializer->unserialize($list) : [];

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

        $settings->set('static:out_of_stock', $serializer->serialize($list));

        return $result;
    }
}
