<?php
namespace BulkGate\Magesms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ContactFormObserver implements ObserverInterface
{
    protected $_magesms;

    public function __construct(
        \BulkGate\Magesms\Bulkgate\MageSMS $mageSMS
    ) {
        $this->_magesms = $mageSMS;
    }

    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        $this->_magesms->runHook('contact_form', new \BulkGate\Extensions\Hook\Variables([
            'customer_email' => trim($request->getParam('email')),
            'customer_name' => trim($request->getParam('name')),
            'customer_phone' => trim($request->getParam('telephone')),
            'customer_message' => trim($request->getParam('comment')),
            'customer_message_short1' => mb_substr(trim($request->getParam('comment')), 120),
            'customer_message_short2' => mb_substr(trim($request->getParam('comment')), 100),
            'customer_message_short3' => mb_substr(trim($request->getParam('comment')), 80),
        ]), $observer );
    }
}
