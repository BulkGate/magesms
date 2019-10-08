<?php
namespace BulkGate\Magesms\Extensions\IO;

use BulkGate\Magesms\Extensions\SettingsInterface;
use BulkGate\Magesms\Extensions\Strict;

/**
 * Class ConnectionFactory
 * @package BulkGate\Magesms\Extensions\IO
 */
class ConnectionFactory extends Strict
{
    /** @var SettingsInterface */
    private $settings;

    /** @var ConnectionInterface */
    private $io;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $url
     * @param string $product
     * @return ConnectionInterface
     */
    public function create($url, $product)
    {
        if ($this->io === null) {
            if (extension_loaded('curl')) {
                $this->io = new Curl(
                    $this->settings->load('static:application_id'),
                    $this->settings->load('static:application_token'),
                    $url,
                    $product,
                    $this->settings->load('main:language', 'en')
                );
            } else {
                $this->io = new FSock(
                    $this->settings->load('static:application_id'),
                    $this->settings->load('static:application_token'),
                    $url,
                    $product,
                    $this->settings->load('main:language', 'en')
                );
            }
        }
        return $this->io;
    }
}
