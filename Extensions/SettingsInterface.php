<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Interface SettingsInterface
 * @package BulkGate\Magesms\Extensions
 */
interface SettingsInterface
{
    /**
     * @param string $settings_key
     * @param bool $default
     * @return mixed
     */
    public function load($settings_key, $default = false);

    /**
     * @param string $settings_key
     * @param mixed|$value
     * @param array $meta
     */
    public function set($settings_key, $value, array $meta = []);

    /**
     * @param string|null $settings_key
     */
    public function delete($settings_key = null);

    /**
     * @return array
     */
    public function synchronize();

    /**
     * @return void
     */
    public function install();

    /**
     * @return void
     */
    public function uninstall();
}
