<?php
namespace BulkGate\Magesms\Extensions\Api;

use BulkGate\Magesms\Extensions;
use stdClass;

/**
 * Class Request
 * @package BulkGate\Magesms\Extensions\Api
 */
class Request extends stdClass implements RequestInterface
{
    /** @var array */
    private $data = [];

    /** @var Extensions\Headers */
    private $headers;

    public function __construct(Extensions\Headers $headers)
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || (isset($_SERVER['REQUEST_METHOD']) &&
                strtolower($_SERVER['REQUEST_METHOD']) !== 'post')) {
            throw new Extensions\API\Exceptions\ConnectionException('Method Not Allowed', 405);
        }

        $this->headers = $headers;

        $content_type = $this->headers->get('Content-Type');

        $data = file_get_contents('php://input');

        if (is_string($data)) {
            if ($content_type === 'application/json') {
                try {
                    $this->data = Extensions\Json::decode($data, Extensions\Json::FORCE_ARRAY);
                } catch (Extensions\Exceptions\JsonException $e) {
                    throw new Extensions\API\Exceptions\ConnectionException('Bad Request', 400);
                }
            } elseif ($content_type === 'application/zip') {
                $this->data = Extensions\Json::decode(
                    Extensions\Compress::decompress($data),
                    Extensions\Json::FORCE_ARRAY
                );
            } else {
                throw new Extensions\API\Exceptions\ConnectionException('Bad Request', 400);
            }
        } else {
            throw new Extensions\API\Exceptions\ConnectionException('Bad Request', 400);
        }
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /** @return Extensions\Headers */
    public function getHeaders()
    {
        return $this->headers;
    }
}
