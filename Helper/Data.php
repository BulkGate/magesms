<?php

namespace BulkGate\Magesms\Helper;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package BulkGate\Magesms\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $configWriter;

    protected $cacheTypeList;

    public function __construct(Context $context, WriterInterface $configWriter, TypeListInterface $cacheTypeList)
    {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function setActivate($enable = true)
    {
        if ($enable === true) {
            $this->configWriter->save('magesms/activated', true);
            $this->configWriter->save('magesms/nonactivated', false);
        } else {
            $this->configWriter->save('magesms/activated', false);
            $this->configWriter->save('magesms/nonactivated', true);
        }

        $this->cacheTypeList->cleanType('config');
        $this->cacheTypeList->cleanType('layout');
        $this->cacheTypeList->cleanType('block_html');
    }
}
