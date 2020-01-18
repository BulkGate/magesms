<?php
namespace BulkGate\Magesms\Setup;

use BulkGate\Magesms\Bulkgate\DIContainer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package BulkGate\Magesms\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    private $dIContainer;

    public function __construct(DIContainer $DIContainer)
    {
        $this->dIContainer = $DIContainer;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '3.0.7', '<')) {
            if (!$setup->tableExists('bulkgate_module')) {
                try {
                    $setup->run("RENAME TABLE `bulkgate_module` TO {$setup->getTable('bulkgate_module')}");
                } catch (\Exception $e) {
                    $this->dIContainer->getSettings()->install();
                }
            }
        }
        $setup->endSetup();
    }
}
