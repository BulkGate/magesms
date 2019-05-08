<?php

namespace BulkGate\Magesms\Block\Adminhtml;

class Menu extends \Magento\Backend\Block\Menu
{
    public function renderNavigation($menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        /** @var \Magento\Backend\Model\Menu\Item $item */
//        foreach ($menu as $item) {
//            if ($item->toArray()['module'] == 'BulkGate_Magesms') {
//                print_r($item->toArray());
//                exit;
//            }
//            exit;
//        }
//        print_r($menu->toArray());
//        exit;
//        return '';
        return parent::renderNavigation($menu, $level, $limit, $colBrakes);
    }
}
