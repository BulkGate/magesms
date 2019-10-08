<?php
namespace BulkGate\Magesms\Observer;

use BulkGate\Magesms\Extensions;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class CustomerRegisterSuccessObserver
 * @package BulkGate\Magesms\Observer
 */
class CustomerRegisterSuccessObserver implements ObserverInterface
{
    protected $_magesms;

    public function __construct(
        \BulkGate\Magesms\Bulkgate\MageSMS $mageSMS
    ) {
        $this->_magesms = $mageSMS;
    }

    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Customer\Controller\Account\CreatePost $controller */
        $controller = $observer->getAccountController();
        $request = $controller->getRequest();

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getCustomer();
        $this->_magesms->runHook('customer_new', new Extensions\Hook\Variables([
            'customer_id' => $customer->getId(),
            'customer_firstname' => $customer->getFirstname(),
            'customer_lastname' => $customer->getLastname(),
            'customer_password' => $request->getParam('password'),
            'customer_email' => $customer->getEmail(),
            'customer_phone' => $request->getParam('telephone'),
        ]), $observer);
    }
}
