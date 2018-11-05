<?php

namespace BulkGate\Magesms\Controller\Adminhtml\Noroute;

use Magento\Framework\App\RequestInterface;

class Index extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
	public function dispatch(RequestInterface $request)
	{
		return parent::dispatch($request);
	}

	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$controllerName = $this->getRequest()->getControllerName();
		$actionName = $this->getRequest()->getActionName();
		if ($actionName == 'index') {
			$actionName = 'default';
		}
		$resultPage->setActiveMenu('BulkGate_Magesms::magesms_'.$controllerName.($actionName!='default'?"_$actionName":''));
		//$this->_addBreadcrumb(__('About'), __('About'));
		//$resultPage->getConfig()->getTitle()->prepend(__('About'));

		$module = $this->getDIContainer()->getModule();
		$settings = $this->getDIContainer()->getSettings();

		$block = $resultPage->getLayout()->getBlock('magesms.page');
		if ($block) {
			$block->setModule($module);
			$block->setSettings($settings);
			$block->setPresenter(str_replace('_', '', ucwords($controllerName, '_')));
			$block->setAction($actionName);
			$block->setTitle($resultPage->getConfig()->getTitle()->get());
			$block->setProxyLinks($this->getProxyLinks($block->getPresenter(), $block->getAction(), $block->getFormKey()));
		}
		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('BulkGate_Magesms::magesms');
	}


}
