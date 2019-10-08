<?php
namespace BulkGate\Magesms\Controller\Adminhtml\Index;

use BulkGate\Magesms\Extensions\IO\Response;
use Magento\Framework\DataObject;

/**
 * Class Ajax
 * @package BulkGate\Magesms\Controller\Adminhtml\Index
 */
class Ajax extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);
        if (!$this->getRequest()->getPost()) {
            $response->setError(true);
            return $this->getResultJsonFactory()->create()->setData($response);
        }
        if (!($action = $this->getRequest()->getParam('action'))) {
            $response->setError(true);
            $response->setMessage(__('Auth error'));
            return $this->getResultJsonFactory()->create()->setData($response);
        }
        switch ($action) {
            case 'authenticate':
                if (!($proxy = $this->getDIContainer()->getProxy()->authenticate())) {
                    $response->setError(true);
                    $response->setMessage(__('Auth error'));
                } else {
                    $response->addData(get_object_vars($proxy));
                }
                break;
            case 'register':
                $proxy = $this->getDIContainer()->getProxy()->register(
                    array_merge(
                        ['name' => 'MageSMS'],
                        $this->getRequest()->getParam('__bulkgate')
                    )
                );
                if ($proxy instanceof Response) {
                    $response->addData(get_object_vars($proxy));
                } else {
                    $response->addData(['token' => $proxy, 'redirect' => $this->getUrl('*/dashboard/')]);
                    $this->getMageHelper()->setActivate();
                }
                break;
            case 'login':
                $proxy = $this->getDIContainer()->getProxy()->login(
                    array_merge(
                        ['name' => 'MageSMS'],
                        $this->getRequest()->getParam('__bulkgate')
                    )
                );
                if ($proxy instanceof Response) {
                    $response->addData(get_object_vars($proxy));
                } else {
                    $response->addData(['token' => $proxy, 'redirect' => $this->getUrl('*/dashboard/')]);
                    $this->getMageHelper()->setActivate();
                }
                break;
            case 'load_module_data':
                $post = $this->getRequest()->getParam('__bulkgate');
                $proxy = $this->getDIContainer()->getProxy()->loadCustomersCount(
                    $post['application_id'],
                    $post['campaign_id']
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'save_module_settings':
                $post = $this->getRequest()->getParam('__bulkgate');
                $proxy = $this->getDIContainer()->getProxy()->saveSettings($post);
                $response->addData(['redirect' => $this->getUrl('*/module_settings')]);
                break;
            case 'save_module_customers':
                $post = $this->getRequest()->getParam('__bulkgate');
                $proxy = $this->getDIContainer()->getProxy()->saveModuleCustomers(
                    $post['application_id'],
                    $post['campaign_id']
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'add_module_filter':
                $post = $this->getRequest()->getParam('__bulkgate');
                $proxy = $this->getDIContainer()->getProxy()->loadCustomersCount(
                    $post['application_id'],
                    $post['campaign_id'],
                    'addFilter',
                    $post
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'remove_module_filter':
                $post = $this->getRequest()->getParam('__bulkgate');
                $proxy = $this->getDIContainer()->getProxy()->loadCustomersCount(
                    $post['application_id'],
                    $post['campaign_id'],
                    'removeFilter',
                    $post
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'save_customer_notifications':
                $post = $this->getRequest()->getParam('__bulkgate', []);
                $proxy = $this->getDIContainer()->getProxy()->saveCustomerNotifications(
                    $post
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'save_admin_notifications':
                $post = $this->getRequest()->getParam('__bulkgate', []);
                $proxy = $this->getDIContainer()->getProxy()->saveAdminNotifications(
                    $post
                );
                $response->addData(get_object_vars($proxy));
                break;
            case 'logout_module':
                $proxy = $this->getDIContainer()->getProxy()->logout();
                $response->addData(['token' => 'guest', 'redirect' => $this->getUrl('*/sign/in')]);
                $this->getMageHelper()->setActivate(false);
                break;
        }
        return $this->getResultJsonFactory()->create()->setData($response);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms');
    }
}
