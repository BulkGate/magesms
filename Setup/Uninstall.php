<?php
namespace BulkGate\Magesms\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use BulkGate\Magesms\Bulkgate\DIContainer;

class Uninstall implements UninstallInterface
{
    private $dIContainer;

    public function __construct(DIContainer $DIContainer)
    {
        $this->dIContainer = $DIContainer;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->dIContainer->getSettings()->uninstall();

        $setup->endSetup();
    }
}
