<?php
namespace BulkGate\Magesms\Extensions\Hook;

use BulkGate\Magesms\Extensions;

/**
 * Class Hook
 * @package BulkGate\Magesms\Extensions\Hook
 */
class Hook extends Extensions\Strict
{
    /** @var string */
    private $url;

    /** @var string */
    private $language_iso;

    /** @var int */
    private $shop_id;

    /** @var Extensions\IO\ConnectionInterface */
    private $connection;

    /** @var Extensions\SettingsInterface */
    private $settings;

    /** @var LoadInterface */
    private $load;

    public function __construct(
        $url,
        $language_iso,
        $shop_id,
        Extensions\IO\ConnectionInterface $connection,
        Extensions\SettingsInterface $settings,
        LoadInterface $load
    ) {
        $this->url = $url;
        $this->language_iso = $settings->load('main:language_mutation', false) ? (string) $language_iso : 'default';
        $this->shop_id = (int) $shop_id;
        $this->connection = $connection;
        $this->settings = $settings;
        $this->load = $load;
    }

    public function run($name, Variables $variables)
    {
        $customer = new Settings((array) $this->settings->load($this->getKey($name, 'customer'), []));
        $admin = new Settings((array) $this->settings->load($this->getKey($name, 'admin'), []));

        if (count($customer->toArray()) > 0 || count($admin->toArray()) > 0) {
            $this->load->load($variables);

            return $this->connection->run(new Extensions\IO\Request($this->url, [
                'customer_sms' => $customer->toArray(),
                'admin_sms' => $admin->toArray(),
                'variables' => $variables->toArray()
            ], true, 5));
        }
        return false;
    }

    private function getKey($name, $type)
    {
        return $type.'_sms-'.($type === 'admin' ? 'default' : $this->language_iso).'-'.$this->shop_id.':'.$name;
    }
}
