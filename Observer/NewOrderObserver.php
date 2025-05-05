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
    /**
     * @var \BulkGate\Magesms\Bulkgate\MageSMS
     */
    protected $_magesms;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * NewOrderObserver constructor.
     * @param \BulkGate\Magesms\Bulkgate\MageSMS $mageSMS
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \BulkGate\Magesms\Bulkgate\MageSMS $mageSMS,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_magesms = $mageSMS;
        $this->_registry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        try {
            // if edited order
            if ($order->getRelationParentId()) {
                // set editing order
                $this->_registry->register('magesms_edit_order', true, true);
                return $this;
            }

            $result = $this->_magesms->runHook('order_new', new Extensions\Hook\Variables([
                'order_status' => strtolower($order->getData('status')),
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'store_id' => $order->getStoreId(),
                'customer_id' => $order->getCustomerId(),
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
                'customer_email' => $order->getCustomerEmail(),
            ]), $observer);

            $this->addComment($order, $result);
        } catch (\Exception $e) {
            //todo
        }

    }

    /**
     * @param $order
     * @param $result
     */
    protected function addComment($order, $result)
    {
        $order->addStatusHistoryComment($this->getMessage($result));
    }


    /**
     * @param $result
     * @return string
     */
    protected function getMessage($result)
    {
        if (!$result) {
            $message = __('Send error. View logs in MageSMS -> SMS History');
        } else if ($result->error) {
            $message = implode('<br>', $result->error);
        } else if (is_string($result)) {
            $message = __('Send error: %1', $result)->render();
        } else {
            $message = 'Ок';
        }

        return '<strong>Order Creation Message.</strong> <strong>Status SMS: </strong>' . $message;
    }
}
