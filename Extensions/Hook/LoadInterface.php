<?php
namespace BulkGate\Magesms\Extensions\Hook;

interface LoadInterface
{
    /**
     * @param Variables $variables
     * @return void
     */
    public function load(Variables $variables);
}
