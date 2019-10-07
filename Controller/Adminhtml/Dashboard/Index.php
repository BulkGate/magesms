<?php
namespace BulkGate\Magesms\Controller\Adminhtml\Dashboard;

class Index extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function execute()
    {
        $this->synchronize();

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('BulkGate_Magesms::magesms_dashboard');
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
            $block->setTitle(__('Dashboard'));
            $block->setProxyLinks($this->getProxyLinks($block->getPresenter(), $block->getAction(), $block->getFormKey()));
            $block->setSalt($this->generateTokens());
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms_dashboard');
    }
}
