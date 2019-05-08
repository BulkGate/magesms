<?php
namespace BulkGate\Magesms\Bulkgate;

use BulkGate\Extensions\Hook\ILoad;
use BulkGate\Extensions\Hook\Variables;
use BulkGate\Extensions\Strict;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer as EventObserver;

class HookLoad extends Strict implements ILoad
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventObserver
     */
    protected $observer;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /** @var \Magento\Checkout\Model\Session */
    protected $sessionCheckout;

    /** @var \Magento\Backend\Model\Auth\Session */
    protected $sessionBackend;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    public function __construct(EventObserver $observer = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->currency = $objectManager->get(\Magento\Directory\Model\Currency::class);
        $this->observer = $observer;
        $this->sessionCheckout = $objectManager->get(\Magento\Checkout\Model\Session::class);
        $this->sessionBackend = $objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
    }

    public function load(Variables $variables)
    {
        $this->order($variables);
        $this->product($variables);
        $this->shop($variables);
    }

    public function order(Variables $variables)
    {
        if (!$this->observer) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        if ($this->observer->getOrder() instanceof \Magento\Sales\Api\Data\OrderInterface) {
            $order = $this->observer->getOrder();
        } elseif ($this->observer->getTrack() instanceof \Magento\Sales\Api\Data\TrackInterface) {
            $order = $this->observer->getTrack()->getShipment()->getOrder();
        }
        if (empty($order)) {
            return;
        }

        $shipping = $order->getShippingAddress();
        if (empty($shipping)) {
            $shipping = $order->getBillingAddress();
        }
        $variables->set('customer_shipping_firstname', $shipping->getFirstname());
        $variables->set('customer_shipping_lastname', $shipping->getLastname());
        $variables->set('customer_company', $shipping->getCompany());
        $variables->set('customer_address', $shipping->getStreetLine(1));
        $variables->set('customer_postcode', $shipping->getPostcode());
        $variables->set('customer_city', $shipping->getCity());
        $variables->set('customer_country', $shipping->getCountry());
        $variables->set('customer_state', $shipping->getRegion());
        $variables->set('customer_phone', $shipping->getTelephone());
        $variables->set('customer_vat_number', $shipping->getVatId());

        $billing = $order->getBillingAddress();
        if (empty($billing)) {
            $billing = $order->getShippingAddress();
        }
        $variables->set('customer_invoice_company', $billing->getCompany());
        $variables->set('customer_invoice_firstname', $billing->getFirstname());
        $variables->set('customer_invoice_lastname', $billing->getLastname());
        $variables->set('customer_invoice_address', $billing->getStreetLine(1));
        $variables->set('customer_invoice_postcode', $billing->getPostcode());
        $variables->set('customer_invoice_city', $billing->getCity());
        $variables->set('customer_invoice_country', $billing->getCountryId());
        $variables->set('customer_invoice_state', $billing->getRegion());
        $variables->set('customer_invoice_phone', $billing->getTelephone());
        $variables->set('customer_invoice_vat_number', $billing->getVatId());

        $variables->set('order_id', $order->getIncrementId());
        $variables->set('order_payment', $order->getPayment()->getMethodInstance()->getTitle());

        $variables->set('order_total_paid', $this->currency->format($order->getGrandTotal(),
            ['display' => \Zend_Currency::NO_SYMBOL], false));
        $variables->set('order_subtotal', $this->currency->format($order->getSubtotal(),
            ['display' => \Zend_Currency::NO_SYMBOL], false));
        $variables->set('order_shipping_amount', $this->currency->format($order->getShippingAmount(),
            ['display' => \Zend_Currency::NO_SYMBOL], false));
        $variables->set('order_currency', $order->getOrderCurrency()->getCurrencyCode());

        $this->formatDateTime($variables, $order->getCreatedAt());

        $variables->set('cart_id', $order->getQuoteId());

        $products = $order->getAllItems();
        $arr = [1 => [], 2 => [],3 => [], 4 => [], 5 => []];
        foreach ($products as $product) {
            $arr[1][] = $product->getProductId().'/'.$product->getName().'/'.$product->getQtyOrdered();
            $arr[2][] = 'id:'.$product->getProductId().', '.__('name').':'.$product->getName().', '.__('qty')
                .':'.$product->getQtyOrdered();
            $arr[3][] = $product->getProductId().'/'.$product->getQtyOrdered();
            $arr[4][] = 'id:'.$product->getProductId().', '.__('qty').':'.$product->getQtyOrdered();
            $arr[5][] = $product->getProductId().'/'.$product->getSku().'/'.$product->getQtyOrdered();
        }
        $variables->set('new_order1', implode('; ', $arr[1]));
        $variables->set('new_order2', implode('; ', $arr[2]));
        $variables->set('new_order3', implode('; ', $arr[3]));
        $variables->set('new_order4', implode('; ', $arr[4]));
        $variables->set('new_order5', implode('; ', $arr[5]));

        if (!($track = $this->registry->registry('magesms_track_obj'))) {
            $track = $order->getTracksCollection()->getLastItem();
        }
        if (empty($track) || !$track->getId()) {
            $params = $this->request->getParams();
            if (!empty($params['tracking'])) {
                $tracking = end($params['tracking']);
                if (!empty($tracking['title']))
                    $track->setTitle($tracking['title']);
                if (!empty($tracking['number']))
                    $track->setTrackNumber($tracking['number']);
            }
        }
        if (!empty($track)) {
            $variables->set('carrier_name', $track->getTitle());
            $variables->set('order_shipping_number', $track->getTrackNumber());
        }

        $admin = $this->sessionBackend->getUser();
        $variables->set('employee_id', $admin->getId());
        $variables->set('employee_email', $admin->getEmail());

    }

    private function formatDateTime(Variables $variables, $date) {
        $variables->set('order_date', $date);
        $parse = date_parse($date);

        $variables->set('order_date1', $parse['day'].'.'.$parse['month'].'.'.$parse['year']);
        $variables->set('order_date2', $parse['day'].'/'.$parse['month'].'/'.$parse['year']);
        $variables->set('order_date3', $parse['day'].'-'.$parse['month'].'-'.$parse['year']);
        $variables->set('order_date4', $parse['year'].'-'.$parse['month'].'-'.$parse['day']);
        $variables->set('order_date5', $parse['month'].'.'.$parse['day'].'.'.$parse['year']);
        $variables->set('order_date6', $parse['month'].'/'.$parse['day'].'/'.$parse['year']);
        $variables->set('order_date7', $parse['month'].'-'.$parse['day'].'-'.$parse['year']);
        $variables->set('order_time', $parse['hour'].':'.sprintf('%02.0f', $parse['minute']));
        $variables->set('order_time1', $parse['hour'].':'.sprintf('%02.0f', $parse['minute'])
            .':'.sprintf('%02.0f', $parse['second']));
    }

    public function product(Variables $variables)
    {
        if (!$this->observer) {
            return;
        }

        /** @var \Magento\CatalogInventory\Model\Stock\Item $item */
        if ($this->observer->getItem() instanceof \Magento\CatalogInventory\Api\Data\StockItemInterface)
            $item = $this->observer->getItem();

        if (empty($item)) {
            return;
        }

        $variables->set('product_quantity', $item->getQty());

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('product');
        if (empty($product)) {
            return;
        }
        $variables->set('product_sku', $product->getSku());
        $variables->set('product_name', $product->getName());
    }

    public function shop(Variables $variables)
    {
        $variables->set('shop_domain', $this->getConfig('web/unsecure/base_url', $variables->get('store_id')));
        $shop_name = $this->getConfig('general/store_information/name', $variables->get('store_id'));
        if ($shop_name) {
            $variables->set('shop_name', $shop_name);
        } else {
            if ($variables->get('store_id')) {
                $variables->set('shop_name', $this->storeManager->getStore($variables->get('store_id'))->getName());
            } else {
                $variables->set('shop_name', $this->storeManager->getStore()->getName());
            }
        }
        $variables->set('shop_email', $this->getConfig('trans_email/ident_general/email', $variables->get('store_id')));
        $variables->set('shop_phone', $this->getConfig('general/store_information/phone', $variables->get('store_id')));
    }

    public function getConfig($config_path, $scopeId = null, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            $scopeType,
            $scopeId
        );
    }
}
