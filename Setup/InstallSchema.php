<?php
namespace BulkGate\Magesms\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use BulkGate\Magesms\Bulkgate\DIContainer;

class InstallSchema implements InstallSchemaInterface
{
    private $dIContainer;

    public function __construct(DIContainer $DIContainer)
    {
        $this->dIContainer = $DIContainer;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->dIContainer->getSettings()->install();

        $setup->endSetup();
    }
}
