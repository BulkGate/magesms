<?php

namespace BulkGate\Magesms\Bulkgate;

use BulkGate\Magesms\Extensions;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class MageSMS
 * @package BulkGate\Magesms\Bulkgate
 */
class MageSMS extends Extensions\Strict implements Extensions\ModuleInterface
{
    const PRODUCT = 'ms';
    private $info = [
        'store' => 'Magento',
        'store_version' => '2.1.x - 2.x.x',
        'name' => 'MageSMS',
        'url' => 'http://www.mage-sms.com',
        'developer' => 'TOPefekt s.r.o.',
        'developer_url' => 'http://www.topefekt.com/',
        'description' => 'MageSMS is a comprehensive and powerful module that enables you to send SMSs to your '.
            'customers or administrators during various events in your WooCommerce store. Improve customer service '.
            '& notify customers via SMS to establish greater levels of trust. Deepen the relationship with your '.
            'customers and build a stronger customer loyalty with the help of SMS marketing. Loyal customers tend '.
            'to buy more & more regularly. And they will frequently recommend your e-shop to others.'.
            ' More customers = higher sales...! Give administrators the advantage of immediate access to information '.
            'via SMS messages, whether they are at a computer or not. With Woo SMS module you can send SMSs worldwide.'.
            ' The price of the SMS depends on the recipient country, selected sender type and the payment amount.'.
            ' Our prices are among the lowest in the market.',
    ];

    /** @var Extensions\Settings */
    public $settings;
    /** @var array */
    private $plugin_data = [];
    public $objectManager;

    public function __construct(Extensions\Settings $settings)
    {
        $this->settings = $settings;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getUrl($path = '')
    {
        return 'https://portal.bulkgate.com'.$path;
    }

    public function statusLoad()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statuses */
        $statuses = $this->objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Status\Collection::class);
        $status_list = (array)$this->settings->load(':order_status_list', null);
        $actual = [];
        foreach ($statuses->toOptionArray() as $status) {
            $actual[$status['value']] = $status['label'];
        }
        if ($status_list !== $actual) {
            $this->settings->set(
                ':order_status_list',
                Extensions\Json::encode($actual),
                ['type' => 'json']
            );
            return true;
        }
        return false;
    }

    public function languageLoad()
    {
        if ((bool)$this->settings->load('main:language_mutation')) {
            $languages = (array)$this->settings->load(':languages', null);

            /** @var \Magento\Framework\Locale\TranslatedLists $translates */
            $translates = $this->objectManager->get(\Magento\Framework\Locale\TranslatedLists::class);
            $locales = $translates->getOptionLocales();

            $langs = [];

            /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
            $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            /** @var \Magento\Store\Model\Store\Interceptor $_lang */
            foreach ($storeManager->getStores() as $_lang) {
                $lang_iso = $_lang->getConfig('general/locale/code');

                if (isset($langs[$lang_iso])) {
                    continue;
                }
                $key = array_search($lang_iso, array_column($locales, 'value'), true);
                $langs[$lang_iso] = $locales[$key]['label'];
            }
            if ($languages !== $langs) {
                $this->settings->set(
                    ':languages',
                    Extensions\Json::encode($langs),
                    ['type' => 'json']
                );
                return true;
            }
            return false;
        } else {
            $this->settings->set(
                ':languages',
                Extensions\Json::encode(['default' => 'Default']),
                ['type' => 'json']
            );
            return true;
        }
    }

    public function storeLoad()
    {
        $actual = [];
        $stores = (array)$this->settings->load(':stores', null);
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        /** @var \Magento\Store\Model\Store\Interceptor $store */
        foreach ($storeManager->getStores() as $id => $store) {
            $actual[$id] = $store->getGroup()->getName().' - '. $store->getName();
        }
        if ($stores !== $actual) {
            $this->settings->set(':stores', Extensions\Json::encode($actual), ['type' => 'json']);
            return true;
        }
        return false;
    }

    public function product()
    {
        return self::PRODUCT;
    }

    public function url()
    {
        return $this->getUrl('/');
    }

    public function info($key = null)
    {
        if (empty($this->plugin_data)) {
            $module = $this->objectManager->get(\Magento\Framework\Module\ModuleList::class)
                ->getOne('BulkGate_Magesms');
            $this->plugin_data = array_merge(
                [
                    'version' => $module['setup_version'],
                    'application_id' => $this->settings->load('static:application_id', -1),
                    'application_product' => $this->product(),
                    'delete_db' => $this->settings->load('main:delete_db', 0),
                    'language_mutation' => $this->settings->load('main:language_mutation', 0)
                ],
                $this->info
            );
        }
        if ($key === null) {
            return $this->plugin_data;
        }
        return isset($this->plugin_data[$key]) ? $this->plugin_data[$key] : null;
    }

    public function runHook($name, Extensions\Hook\Variables $variables, EventObserver $observer)
    {
        /** @var DIContainer $di */
        $di = $this->objectManager->get(DIContainer::class);
        $hook = new Extensions\Hook\Hook(
            $di->getModule()->getUrl('/module/hook'),
            $variables->get('lang_id'),
            $variables->get('store_id'),
            $di->getConnection(),
            $di->getSettings(),
            new HookLoad($observer)
        );

        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        if (is_callable([$observer, 'getStoreId'])) {
            $storeId = $observer->getStoreId();
        } elseif (is_callable([$observer, 'getStore'])) {
            $storeId = $observer->getStore()->getId();
        } elseif ($storeManager->getStore()) {
            $storeId = $storeManager->getStore()->getStoreId();
        } else {
            $storeId = null;
        }

        $variables->set('store_id', $storeId);

        try {
            $hook->run((string)$name, $variables);
            return true;
        } catch (Extensions\IO\Exceptions\InvalidResultException $e) {
            return false;
        }
    }
}
