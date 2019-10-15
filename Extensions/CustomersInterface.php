<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Interface CustomersInterface
 * @package BulkGate\Magesms\Extensions
 */
interface CustomersInterface
{
    /**
     * @param array $filter
     * @return array
     */
    public function loadCount(array $filter = []);

    /**
     * @param array $filter
     * @return array
     */
    public function load(array $filter = []);
}
