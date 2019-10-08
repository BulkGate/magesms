<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Interface ModuleInterface
 * @package BulkGate\Magesms\Extensions
 */
interface ModuleInterface
{
    /**
     * @param string $path
     * @return string
     */
    public function getUrl($path = '');

    /** @return bool */
    public function statusLoad();

    /** @return bool */
    public function languageLoad();

    /** @return bool */
    public function storeLoad();

    /** @return string */
    public function product();

    /** @return string */
    public function url();

    /**
     * @param string|null $key
     * @return string|array
     */
    public function info($key = null);
}
