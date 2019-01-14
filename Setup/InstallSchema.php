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
        $this->installOrder($setup);
        $this->installCharge($setup);
        $this->installTransaction($setup);

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
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'mundipagg_id',
                    Table::TYPE_TEXT,
                    21,
                    [
                        'nullable' => false
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

    public function installOrder(
        SchemaSetupInterface $installer
    ) {
        $tableName = $installer->getTable('mundipagg_module_core_order');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $webhookTable = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'mundipagg_id',
                    Table::TYPE_TEXT,
                    19,
                    [
                        'nullable' => false
                    ],
                    'format: or_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false,
                    ],
                    'Code'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    30,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Status'
                )
                ->setComment('Order Table')
                ->setOption('charset', 'utf8')
            ;

            $installer->getConnection()->createTable($webhookTable);
        }
        return $installer;
    }

    public function installCharge(
        SchemaSetupInterface $installer
    ) {
        $tableName = $installer->getTable('mundipagg_module_core_charge');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $webhookTable = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'mundipagg_id',
                    Table::TYPE_TEXT,
                    19,
                    [
                        'nullable' => false
                    ],
                    'format: ch_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    19,
                    [
                        'nullable' => false
                    ],
                    'format: or_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false,
                    ],
                    'Code'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'amount'
                )
                ->addColumn(
                    'paid_amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Paid Amount'
                )
                ->addColumn(
                    'canceled_amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Canceled Amount'
                )
                ->addColumn(
                    'refunded_amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Refunded Amount'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    30,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Status'
                )
                ->setComment('Charge Table')
                ->setOption('charset', 'utf8')
            ;

            $installer->getConnection()->createTable($webhookTable);
        }
        return $installer;
    }

    public function installTransaction(
        SchemaSetupInterface $installer
    ) {
        $tableName = $installer->getTable('mundipagg_module_core_transaction');
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $webhookTable = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'mundipagg_id',
                    Table::TYPE_TEXT,
                    21,
                    [
                        'nullable' => false
                    ],
                    'format: tran_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'charge_id',
                    Table::TYPE_TEXT,
                    19,
                    [
                        'nullable' => false
                    ],
                    'format: ch_xxxxxxxxxxxxxxxx'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'amount'
                )
                ->addColumn(
                    'paid_amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'paid amount'
                )
                ->addColumn(
                    'acquirer_tid',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'acquirer tid'
                )
                ->addColumn(
                    'acquirer_nsu',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'acquirer nsu'
                )
                ->addColumn(
                    'acquirer_auth_code',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'acquirer auth code'
                )
                ->addColumn(
                    'acquirer_name',
                    Table::TYPE_TEXT,
                    300,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Type'
                )
                ->addColumn(
                    'acquirer_message',
                    Table::TYPE_TEXT,
                    300,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Type'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    30,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    30,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [

                        'nullable' => false,
                    ],
                    'Created At'
                )
                ->setComment('Transaction Table')
                ->setOption('charset', 'utf8')
            ;

            $installer->getConnection()->createTable($webhookTable);
        }
        return $installer;
    }
}