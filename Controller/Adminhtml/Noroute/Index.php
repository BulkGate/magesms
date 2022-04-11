<?php

namespace BulkGate\Magesms\Controller\Adminhtml\Noroute;

/**
 * Class Index
 * @package BulkGate\Magesms\Controller\Adminhtml\Noroute
 */
class Index extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        if ($actionName === 'index') {
            $actionName = 'default';
        }
        $resultPage->addPageLayoutHandles([], 'magesms_default', false);
        $resultPage->setActiveMenu('BulkGate_Magesms::magesms');

        $module = $this->getDIContainer()->getModule();
        $settings = $this->getDIContainer()->getSettings();

        $block = $resultPage->getLayout()->getBlock('magesms.page');
        if ($block) {
            $titleSource = 'BulkGate_Magesms::magesms_'.$controllerName;
            if ($actionName !== 'default') {
                $titleSource .= '_'.$actionName;
            }
            /** @var $menu \Magento\Backend\Model\Menu\Config */
            $menu = $this->getObjectManager()->get(\Magento\Backend\Model\Menu\Config::class)->getMenu()
                ->get('BulkGate_Magesms::magesms');
            $title = $menu->getChildren()
                ->get($titleSource);
            if ($title) {
                $title = $title->getTitle();
            } else {
                $title = $menu->getChildren()->get('BulkGate_Magesms::magesms_'.$controllerName);
                $title = $title ? $title->getTitle() : $controllerName;
            }
            $block->setModule($module);
            $block->setSettings($settings);
            $block->setPresenter(str_replace('_', '', ucwords($controllerName, '_')));
            $block->setAction($actionName);
            $block->setTitle($title);
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
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms');
    }
}
