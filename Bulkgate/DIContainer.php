<?php
namespace BulkGate\Magesms\Bulkgate;

use BulkGate, BulkGate\Extensions;

class DIContainer extends Extensions\DIContainer
{
    protected function createDatabase()
    {
        return new Database();
    }

    protected function createModule()
    {
        return new MageSMS($this->getService('settings'));
    }

    protected function createCustomers()
    {
        return new Customers($this->getService('database'));
    }

}