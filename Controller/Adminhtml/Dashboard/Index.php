<?php
namespace Topefekt\Magesms\Controller\Adminhtml\Dashboard;

class Index extends \Topefekt\Magesms\Controller\Adminhtml\Action
{
	public function execute()
	{
		$this->synchronize();

		$resultPage = $this->_resultPageFactory->create();
		$resultPage->setActiveMenu('Topefekt_Magesms::magesms_dashboard');
		$this->_addBreadcrumb(__('Dashboard'), __('Dashboard'));
		$resultPage->getConfig()->getTitle()->prepend(__('Dashboard'));

		$module = $this->getDIContainer()->getModule();
		$settings = $this->getDIContainer()->getSettings();

		$block = $resultPage->getLayout()->getBlock('magesms.page');
		if ($block) {
			$block->setModule($module);
			$block->setSettings($settings);
			$block->setPresenter('Dashboard');
			$block->setAction('default');
			$block->setTitle($resultPage->getConfig()->getTitle()->get());
			$block->setProxyLinks($this->getProxyLinks($block->getPresenter(), $block->getAction(), $block->getFormKey()));
		}
		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms_dashboard');
	}

}
