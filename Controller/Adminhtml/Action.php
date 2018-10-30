<?php

namespace Topefekt\Magesms\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Topefekt\Magesms\Bulkgate\DIContainer;
use BulkGate\Extensions\IO\InvalidResultException;
use Topefekt\Magesms\Bulkgate\MageSMS;
use Topefekt\Magesms\Helper\Data;

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

		if (!$application_token && !in_array($request->getControllerName(), array('index', 'sign')))
		{
			$this->getMessageManager()->addSuccessMessage(__('Not registered yet? Create account now!'));
			return $this->_redirect('*/sign/in');
		}
//		print_r($this->getRequest()->getParams());
//		echo get_class($this->getRequest());
//		echo "action-";

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
				return array('_generic' => array('save' => array(
					'url' => $url,
					'params' => array('action' => 'save_customer_notifications', 'form_key' => $form_key)
				)));
				break;
			case 'ModuleNotifications:admin':
				return array('_generic' => array('save' => array(
					'url' => $url,
					'params' => array('action' => 'save_admin_notifications', 'form_key' => $form_key)
				)));
				break;
			case 'Sign:up':
				return array('_generic' => array('register' => array(
					'url' => $url,
					'params' => array('action' => 'register', 'form_key' => $form_key)
				)));
				break;
			case 'ModuleSign:in':
				return array('_generic' => array('login' => array(
					'url' => $url,
					'params' => array('action' => 'login', 'form_key' => $form_key)
				)));
				break;
			case 'ModuleSettings:default':
				return array('_generic' => array(
					'save' => array(
						'url' => $url,
						'params' => array('action' => 'save_module_settings', 'form_key' => $form_key)
					),
					'logout' => array(
						'url' => $url,
						'params' => array('action' => 'logout_module', 'form_key' => $form_key)
					)));
				break;
			case 'SmsCampaign:campaign':
				return array('campaign' => array(
					'loadModuleData' => array(
						'url' => $url,
						'params' => array('action' => 'load_module_data', 'form_key' => $form_key)
					),
					'saveModuleCustomers' => array(
						'url' => $url,
						'params' => array('action' => 'save_module_customers', 'form_key' => $form_key)
					),
					'addModuleFilter' => array(
						'url' => $url,
						'params' => array('action' => 'add_module_filter', 'form_key' => $form_key)
					),
					'removeModuleFilter' => array(
						'url' => $url,
						'params' => array('action' => 'remove_module_filter', 'form_key' => $form_key)
					)
				));
				break;
			default:
				return array();
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
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms');
	}

	public function _initAction()
	{
		$this->_view->loadLayout();
		$this->_addBreadcrumb(__('MageSMS'), __('MageSMS'));
		return $this;
	}

}
