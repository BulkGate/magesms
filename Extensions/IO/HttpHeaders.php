<?php
namespace BulkGate\Magesms\Extensions\IO;

use BulkGate\Magesms\Extensions\Strict;

/**
 * Class HttpHeaders
 * @package BulkGate\Magesms\Extensions\IO
 */
class HttpHeaders extends Strict
{
    /** @var array */
    private $headers = [];

    public function __construct($raw_header)
    {
        $this->parseHeaders($raw_header);
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getHeader($name, $default = null)
    {
        $name = strtolower($name);

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return $default;
    }

    public function getContentType()
    {
        $content_type = $this->getHeader('content-type');

        if ($content_type !== null) {
            preg_match('~^(?<type>[a-zA-Z]*/[a-zA-Z]*)~', trim($content_type), $type);

            if (isset($type['type'])) {
                return $type['type'];
            }
        }
        return '';
    }

    /** @return array */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $raw_header
     */
    private function parseHeaders($raw_header)
    {
        if (!is_array($raw_header)) {
            $raw_header = explode("\r\n\r\n", $raw_header);
        }

        foreach ($raw_header as $index => $request) {
            foreach (explode("\r\n", $request) as $i => $line) {
                if (strlen($line) > 0) {
                    if ((int)$i === 0) {
                        $this->headers['http_code'] = $line;
                    } else {
                        list ($key, $value) = explode(':', $line);
                        $this->headers[strtolower($key)] = trim($value);
                    }
                }
            }
        }
    }
}
