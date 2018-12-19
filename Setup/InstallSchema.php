<?php


namespace MundiPagg\MundiPagg\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $this->installWebhook($setup);

        $setup->endSetup();
    }


    public function installWebhook(
        SchemaSetupInterface $installer
    ) {
        $tableName = $installer->getTable('mundipagg_module_core_webhook');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $webhookTable = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_TEXT,
                    21,
                    [
                        'nullable' => false,
                        'primary' => true
                    ],
                    'format: hook_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'handled_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT
                    ],
                    'When the webhook was handled.'
                )
                ->setComment('Webhook Table')
                ->setOption('charset', 'utf8')
            ;

            $installer->getConnection()->createTable($webhookTable);
        }
        return $installer;
    }
}