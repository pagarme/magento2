<?php

namespace Pagarme\Pagarme\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface   $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();
        $version = $context->getVersion();
        $installSchema = new InstallSchema();

        if (version_compare($version, "1.1.0", "<")) {
            $setup = $installSchema->installHubToken($setup);
        }

        if (version_compare($version, '2.2.5', '<')) {
            $connection = $setup->getConnection();
            if ($connection->tableColumnExists(
                'pagarme_module_core_recurrence_products_plan',
                'apply_discount_in_all_product_cycles'
            ) === false) {
                $connection->addColumn(
                    $setup->getTable('pagarme_module_core_recurrence_products_plan'),
                    'apply_discount_in_all_product_cycles',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'length' => 1,
                        'nullable' => true,
                        'comment' => 'Apply products cycle to discount'
                    ]
                );
            }

            if ($connection->tableColumnExists(
                'pagarme_module_core_recurrence_products_subscription',
                'apply_discount_in_all_product_cycles'
            ) === false) {
                $connection->addColumn(
                    $setup->getTable('pagarme_module_core_recurrence_products_subscription'),
                    'apply_discount_in_all_product_cycles',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'length' => 1,
                        'nullable' => true,
                        'comment' => 'Apply products cycle to discount'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '2.5.0', '<')) {
            if ($setup->getConnection()->tableColumnExists('pagarme_module_core_recipients', 'type') === false) {
                $setup->getConnection()->changeColumn(
                    $setup->getTable('pagarme_module_core_recipients'),
                    'document_type',
                    'type',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 11,
                        'nullable' => 'false',
                        'comment' => 'Recipient document type: individual (CPF) or corporation (CNPJ)'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
