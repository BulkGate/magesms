<?php
namespace Topefekt\Magesms\Controller\Adminhtml\Index;

class About extends \Topefekt\Magesms\Controller\Adminhtml\Action
{
	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$resultPage->setActiveMenu('Topefekt_Magesms::magesms_about');
		$this->_addBreadcrumb(__('About'), __('About'));
		$resultPage->getConfig()->getTitle()->prepend(__('About'));


		$module = $this->getDIContainer()->getModule();
		$settings = $this->getDIContainer()->getSettings();

		$block = $resultPage->getLayout()->getBlock('magesms.page');
		if ($block) {
			$block->setModule($module);
			$block->setSettings($settings);
			$block->setPresenter('ModuleAbout');
			$block->setAction('default');
			$block->setTitle($resultPage->getConfig()->getTitle()->get());
		}
		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms_about');
	}

}
