<?php
namespace BulkGate\Magesms\Controller\Adminhtml\Sign;

use BulkGate\Magesms\Controller\Adminhtml\Action;

/**
 * Class In
 * @package BulkGate\Magesms\Controller\Adminhtml\Sign
 */
class In extends Action
{
    public function execute()
    {
        $this->synchronize();

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addPageLayoutHandles([], 'magesms_default', false);
        $resultPage->setActiveMenu('BulkGate_Magesms::magesms_sign_in');
        $this->_addBreadcrumb(__('Sign-in'), __('Sign-in'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sign-in'));

        $module = $this->getDIContainer()->getModule();
        $settings = $this->getDIContainer()->getSettings();

        $block = $resultPage->getLayout()->getBlock('magesms.page');
        if ($block) {
            $block->setModule($module);
            $block->setSettings($settings);
            $block->setPresenter('ModuleSign');
            $block->setAction('in');
            $block->setTitle($resultPage->getConfig()->getTitle()->get());
            $block->setProxyLinks(
                $this->getProxyLinks(
                    $block->getPresenter(),
                    $block->getAction(),
                    $block->getFormKey()
                )
            );
            $block->setSalt($this->generateTokens());
        }

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms_sign_in');
    }
}
