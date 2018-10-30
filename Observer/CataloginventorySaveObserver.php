<?php
namespace Topefekt\Magesms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class CataloginventorySaveObserver implements ObserverInterface
{
	protected $_magesms;
	protected $_registry;

	public function __construct(
		\Topefekt\Magesms\Bulkgate\MageSMS $mageSMS,
		\Magento\Framework\Registry $registry
	) {
		$this->_magesms = $mageSMS;
		$this->_registry = $registry;
	}

	public function execute(EventObserver $observer)
	{
		/** @var \Magento\CatalogInventory\Model\Stock\Item $item */
		$item = $observer->getEvent()->getItem();

		if ($item->getManageStock()) {
			if (!($origData = $this->_registry->registry('magesms_stock_item_' . $item->getProductId()))) {
				$origData = $item->getOrigData();
			}
			if (!$origData)
				return $this;
			if ($item->hasDataChanges()) {
				if ($origData['qty'] > 0 && $item->getQty() <= 0) {
					$this->_magesms->runHook('product_out_of_stock', new \BulkGate\Extensions\Hook\Variables(array(
						'product_id' => $item->getProductId(),
					)), $observer);
				}
				if ($item->getNotifyStockQty() > $item->getQty() && $origData['qty'] >= $item->getNotifyStockQty()) {
					$this->_magesms->runHook('product_low_stock', new \BulkGate\Extensions\Hook\Variables(array(
						'product_id' => $item->getProductId(),
					)), $observer);
				}
			}
		}
	}
}
