<?php

namespace BulkGate\Magesms\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $configWriter;

    public function __construct(Context $context, WriterInterface $configWriter)
    {
        parent::__construct($context);
        $this->configWriter = $configWriter;
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
    }

}
