<?php
namespace BulkGate\Magesms\Extensions\Hook;

use BulkGate\Magesms\Extensions\Database;

interface ExtensionInterface
{
    /**
     * @param Database\DatabaseInterface $database
     * @param Variables $variables
     * @return void
     */
    public function extend(Database\DatabaseInterface $database, Variables $variables);
}
