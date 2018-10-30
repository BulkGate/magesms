<?php

namespace Topefekt\Magesms\Controller\Adminhtml;

use Magento\Framework\App\RequestInterface;

class Noroute extends Action
{
	public function execute()
	{
		return true;
	}

	protected function _isAllowed()
	{
		return true;
		return $this->_authorization->isAllowed('Topefekt_Magesms::magesms_about');
	}

}
