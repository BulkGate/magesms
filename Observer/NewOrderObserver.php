<?php
namespace BulkGate\Magesms\Observer;

use BulkGate\Magesms\Extensions;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class NewOrderObserver
 * @package BulkGate\Magesms\Observer
 */
class NewOrderObserver implements ObserverInterface
{
    protected $_magesms;
    protected $_registry;

    public function __construct(
        \BulkGate\Magesms\Bulkgate\MageSMS $mageSMS,
        \Magento\Framework\Registry $registry
    ) {
        $this->_magesms = $mageSMS;
        $this->_registry = $registry;
    }

    public function execute(EventObserver $observer)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        // if edited order
        if ($order->getRelationParentId()) {
            // set editing order
            $this->_registry->register('magesms_edit_order', true, true);
            return $this;
        }

        $this->_magesms->runHook('order_new', new Extensions\Hook\Variables([
            'order_status' => $order->getData('status'),
            'order_id' => $order->getId(),
            'store_id' => $order->getStoreId(),
            'customer_id' => $order->getCustomerId(),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'customer_email' => $order->getCustomerEmail(),
        ]), $observer);
    }
}
