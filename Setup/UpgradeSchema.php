<?php

namespace Pagarme\Pagarme\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $version = $context->getVersion();
        $installSchema = new InstallSchema();

        if (version_compare($version, "1.1.0", "<")) {
            $setup = $installSchema->installHubToken($setup);
        }

        $setup->endSetup();
    }
}
