<?php
namespace BulkGate\Magesms\Extensions\IO;

/**
 * Interface ConnectionInterface
 * @package BulkGate\Magesms\Extensions\IO
 */
interface ConnectionInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request);
}
