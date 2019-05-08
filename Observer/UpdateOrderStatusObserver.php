<?php
namespace BulkGate\Magesms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class UpdateOrderStatusObserver implements ObserverInterface
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
        // if editing order
        if ($this->_registry->registry('magesms_edit_order'))
            return $this;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        if ($order->getOrigData('status') != $order->getData('status')) {
            $this->_magesms->runHook('order_status_change_'.$order->getData('status'), new \BulkGate\Extensions\Hook\Variables(array(
                'customer_id' => $order->getCustomerId(),
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
                'customer_email' => $order->getCustomerEmail(),
            )), $observer );
        }
    }
}
