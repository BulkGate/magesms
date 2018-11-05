<?php
namespace BulkGate\Magesms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class UpdateOrderTrackingNumberObserver implements ObserverInterface
{
	protected $_magesms;
	protected $_registry;

	public function __construct(
		\BulkGate\Magesms\Bulkgate\MageSMS $mageSMS,
		\Magento\Framework\Registry $registry
	) {
		$this->_magesms = $mageSMS;
		$this->_registry = $registry;
	}

	public function execute(EventObserver $observer)
	{
		/** @var \Magento\Sales\Model\Order\Shipment\Track $track */
		$track = $observer->getTrack();

		if ($track->hasDataChanges() && ($track->getCreatedAt() == $track->getUpdatedAt() || $track->dataHasChangedFor('track_number'))) {
			if ($this->_registry->registry('magesms_track_obj'))
				$this->_registry->unregister('magesms_track_obj');
			$this->_registry->register('magesms_track_obj', $track);

			$this->_magesms->runHook('update_order_tracking_number', new \BulkGate\Extensions\Hook\Variables(array(
				'customer_id' => $track->getShipment()->getCustomerId(),
				'customer_firstname' => $track->getShipment()->getCustomerFirstname(),
				'customer_lastname' => $track->getShipment()->getCustomerLastname(),
				'customer_email' => $track->getShipment()->getCustomerEmail(),
			)), $observer );
		}
	}
}
