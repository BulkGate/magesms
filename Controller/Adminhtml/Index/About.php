<?php
namespace BulkGate\Magesms\Controller\Adminhtml\Index;

class About extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
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
//            die($resultPage->getConfig()->getTitle()->get());
            $block->setTitle(__('About'));
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms_about');
    }

}
