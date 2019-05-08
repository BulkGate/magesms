<?php

namespace BulkGate\Magesms\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use BulkGate\Magesms\Bulkgate\DIContainer;
use BulkGate\Extensions\IO\InvalidResultException;
use BulkGate\Magesms\Bulkgate\MageSMS;
use BulkGate\Magesms\Helper\Data;

abstract class Action extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $_coreRegistry;
    protected $_resultJsonFactory;
    protected $dIContainer;
    protected $_mageHelper;

    public function __construct(Context $context, PageFactory $resultPageFactory, Registry $registry,
                                DIContainer $dIContainer, JsonFactory $resultJsonFactory, Data $data)
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dIContainer = $dIContainer;
        $this->_mageHelper = $data;
    }

    public function dispatch(RequestInterface $request)
    {
        $this->synchronize();
        $settings = $this->getDIContainer()->getSettings();
        $application_token = $settings->load('static:application_token', false);

        if (!$application_token && !in_array($request->getControllerName(), ['index', 'sign']))
        {
            $this->getMessageManager()->addSuccessMessage(__('Not registered yet? Create account now!'));
            return $this->_redirect('*/sign/in');
        }
//        print_r($this->getRequest()->getParams());
//        echo get_class($this->getRequest());
//        echo "action-";

        return parent::dispatch($request);
    }

    public function getDIContainer()
    {
        return $this->dIContainer;
    }

    public function synchronize($now = false)
    {
        /** @var MageSMS $module */
        $module = $this->getDIContainer()->getModule();
        $now = $now || $module->statusLoad() || $module->languageLoad() || $module->storeLoad();
        try
        {
            $this->getDIContainer()->getSynchronize()->run($module->getUrl('/module/settings/synchronize'), $now);
            return true;
        } catch (InvalidResultException $e)
        {
            return false;
        }
    }

    function getProxyLinks($presenter, $action, $form_key = '')
    {
        $url = $this->getUrl('*/index/ajax').'?isAjax=true';
        switch ($presenter.':'.$action) {
            case 'ModuleNotifications:customer':
                return ['_generic' => ['save' => [
                    'url' => $url,
                    'params' => ['action' => 'save_customer_notifications', 'form_key' => $form_key]
                ]]];
                break;
            case 'ModuleNotifications:admin':
                return ['_generic' => ['save' => [
                    'url' => $url,
                    'params' => ['action' => 'save_admin_notifications', 'form_key' => $form_key]
                ]]];
                break;
            case 'Sign:up':
                return ['_generic' => ['register' => [
                    'url' => $url,
                    'params' => ['action' => 'register', 'form_key' => $form_key]
                ]]];
                break;
            case 'ModuleSign:in':
                return ['_generic' => ['login' => [
                    'url' => $url,
                    'params' => ['action' => 'login', 'form_key' => $form_key]
                ]]];
                break;
            case 'ModuleSettings:default':
                return ['_generic' => [
                    'save' => [
                        'url' => $url,
                        'params' => ['action' => 'save_module_settings', 'form_key' => $form_key]
                    ],
                    'logout' => [
                        'url' => $url,
                        'params' => ['action' => 'logout_module', 'form_key' => $form_key]
                    ]]];
                break;
            case 'SmsCampaign:campaign':
                return ['campaign' => [
                    'loadModuleData' => [
                        'url' => $url,
                        'params' => ['action' => 'load_module_data', 'form_key' => $form_key]
                    ],
                    'saveModuleCustomers' => [
                        'url' => $url,
                        'params' => ['action' => 'save_module_customers', 'form_key' => $form_key]
                    ],
                    'addModuleFilter' => [
                        'url' => $url,
                        'params' => ['action' => 'add_module_filter', 'form_key' => $form_key]
                    ],
                    'removeModuleFilter' => [
                        'url' => $url,
                        'params' => ['action' => 'remove_module_filter', 'form_key' => $form_key]
                    ]
                ]];
                break;
            default:
                return [];
        }
    }

    public function getResultJsonFactory()
    {
        return $this->_resultJsonFactory;
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function getCoreRegistry()
    {
        return $this->_coreRegistry;
    }

    public function getMageHelper()
    {
        return $this->_mageHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms');
    }

    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('MageSMS'), __('MageSMS'));
        return $this;
    }

}
