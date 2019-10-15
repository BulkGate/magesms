<?php
namespace BulkGate\Magesms\Extensions\Api;

/**
 * Interface RequestInterface
 * @package BulkGate\Magesms\Extensions\Api
 */
interface RequestInterface
{
    /**
     * @return \BulkGate\Magesms\Extensions\Headers
     */
    public function getHeaders();
}
