<?php

namespace BulkGate\Magesms\Controller\Adminhtml\Noroute;

use Magento\Framework\App\RequestInterface;

class Index extends \BulkGate\Magesms\Controller\Adminhtml\Action
{
    public function dispatch(RequestInterface $request)
    {
        return parent::dispatch($request);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        if ($actionName == 'index') {
            $actionName = 'default';
        }
        $resultPage->setActiveMenu('BulkGate_Magesms::magesms');

        $module = $this->getDIContainer()->getModule();
        $settings = $this->getDIContainer()->getSettings();

        $block = $resultPage->getLayout()->getBlock('magesms.page');
        if ($block) {
            $titleSource = 'BulkGate_Magesms::magesms_'.$controllerName;
            if ($actionName !== 'default') {
                $titleSource .= '_'.$actionName;
            }
            $object = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var $menu \Magento\Backend\Model\Menu */
            $menu = $object->get(\Magento\Backend\Model\Menu\Config::class)->getMenu()
                ->get('BulkGate_Magesms::magesms');
            $title = $menu->getChildren()
                ->get($titleSource);
            if ($title) {
                $title = $title->getTitle();
            } else {
                $title = $menu->getChildren()->get('BulkGate_Magesms::magesms_'.$controllerName)->getTitle();

            }
            $block->setModule($module);
            $block->setSettings($settings);
            $block->setPresenter(str_replace('_', '', ucwords($controllerName, '_')));
            $block->setAction($actionName);
            $block->setTitle($title);
            $block->setProxyLinks($this->getProxyLinks($block->getPresenter(), $block->getAction(),
                $block->getFormKey()));
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BulkGate_Magesms::magesms');
    }
}
