<?php
namespace BulkGate\Magesms\Extensions\Api;

use BulkGate\Magesms\Extensions;

/**
 * Class Response
 * @package BulkGate\Magesms\Extensions\Api
 */
class Response extends Extensions\Strict implements ResponseInterface
{
    /** @var mixed */
    private $payload;

    /** @var string */
    private $contentType;

    public function __construct($payload, $compressed = false)
    {
        $this->payload = $payload;
        $this->contentType = $compressed ? 'application/zip' : 'application/json';
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function send()
    {
        header("Content-Type: {$this->contentType}; charset=utf-8");

        if ($this->contentType === 'application/zip') {
            echo Extensions\Compress::compress(Extensions\Json::encode($this->payload));
        } else {
            echo Extensions\Json::encode($this->payload);
        }
    }
}
