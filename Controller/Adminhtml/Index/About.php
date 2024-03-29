<?php
namespace BulkGate\Magesms\Controller\Adminhtml\Index;

/**
 * Class About
 * @package BulkGate\Magesms\Controller\Adminhtml\Index
 */
class About extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addPageLayoutHandles([], 'magesms_default', false);
        $resultPage->setActiveMenu('BulkGate_Magesms::magesms_about');
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
            $block->setTitle(__('About'));
            $block->setSalt($this->generateTokens());
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms_about');
    }
}
