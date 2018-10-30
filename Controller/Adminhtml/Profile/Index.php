<?php
namespace Topefekt\Magesms\Controller\Adminhtml\Index;

use BulkGate\Extensions;

class Signin extends \Topefekt\Magesms\Controller\Adminhtml\Action
{
	public function execute()
	{
		die('bbbb');

		$resultPage = $this->_resultPageFactory->create();
		$resultPage->setActiveMenu('Topefekt_Magesms::magesms_signin');
		$this->_addBreadcrumb(__('Sign-in'), __('Sign-in'));
		$resultPage->getConfig()->getTitle()->prepend(__('Sign-in'));

		$block = $resultPage->getLayout()->getBlock('magesms.index.about');
		$block->setModuleVersion($this->_objectManager->get('Topefekt\Magesms\Helper\Data')->getModuleVersion());

		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms_signin');
	}

}
