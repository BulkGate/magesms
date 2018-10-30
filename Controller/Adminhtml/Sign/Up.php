<?php
namespace Topefekt\Magesms\Controller\Adminhtml\Sign;

class Up extends \Topefekt\Magesms\Controller\Adminhtml\Action
{
	public function execute()
	{
		$this->synchronize();

		$resultPage = $this->_resultPageFactory->create();
		$resultPage->setActiveMenu('Topefekt_Magesms::magesms_sign_up');
		$this->_addBreadcrumb(__('Sign-in'), __('Sign-up'));
		$resultPage->getConfig()->getTitle()->prepend(__('Sign-up'));

		$module = $this->getDIContainer()->getModule();
		$settings = $this->getDIContainer()->getSettings();

		$block = $resultPage->getLayout()->getBlock('magesms.page');
		if ($block) {
			$block->setModule($module);
			$block->setSettings($settings);
			$block->setPresenter('Sign');
			$block->setAction('up');
			$block->setTitle($resultPage->getConfig()->getTitle()->get());
			$block->setProxyLinks($this->getProxyLinks($block->getPresenter(), $block->getAction(), $block->getFormKey()));
		}
		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms_sign_up');
	}

}
