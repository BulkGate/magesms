<?php
namespace BulkGate\Magesms\Extensions\Api;

use BulkGate\Magesms\Extensions;

/**
 * Class Api
 * @package BulkGate\Magesms\Extensions\Api
 */
abstract class Api extends Extensions\Strict
{
    /** @var Extensions\Database\DatabaseInterface */
    protected $database;

    /** @var Extensions\SettingsInterface */
    protected $settings;

    public function __construct(
        $action,
        Extensions\Api\RequestInterface $data,
        Extensions\Database\DatabaseInterface $database,
        Extensions\SettingsInterface $settings
    ) {
        $this->database = $database;
        $this->settings = $settings;

        $method = 'action'.ucfirst($action);

        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], [$data]);
        } else {
            throw new Exceptions\ConnectionException('Not Found', 404);
        }
    }

    public function sendResponse(ResponseInterface $response)
    {
        $response->send();
        exit;
    }
}
