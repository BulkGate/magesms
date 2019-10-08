<?php
namespace BulkGate\Magesms\Bulkgate;

use BulkGate\Magesms\Extensions;

/**
 * Class Api
 * @package BulkGate\Magesms\Bulkgate
 */
class Api extends Extensions\Api\Api
{
    public function actionCampaignCustomerCount(Extensions\Api\RequestInterface $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Extensions\Api\Response($customers->loadCount($data->filter), true));
    }

    public function actionCampaignCustomer(Extensions\Api\RequestInterface $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Extensions\Api\Response($customers->load($data->filter), true));
    }
}
