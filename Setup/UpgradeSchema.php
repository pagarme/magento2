<?php
namespace MundiPagg\MundiPagg\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

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

        if (version_compare($version, "1.0.2", "<")) {
            $setup = $this->updateVersionOneZeroTwo($setup);
        }

        if (version_compare($version, "1.0.14", "<")) {
            $setup = $this->updateVersionOneZeroTwelve($setup);
        }

//Mundipagg Module Core tables
        $installSchema = new InstallSchema();

        if (version_compare($version, "1.3.0", "<")) {
            $setup = $installSchema->installWebhook($setup);
            $setup = $installSchema->installOrder($setup);
            $setup = $installSchema->installCharge($setup);
            $setup = $installSchema->installTransaction($setup);
        }

        if (version_compare($version, "1.4.0", "<")) {
            $setup = $installSchema->installConfig($setup);
            $setup = $this->fixTransactionTable($setup);
        }
        if (version_compare($version, "1.7.0", "<")) {
            $setup = $installSchema->installSavedCard($setup);
            $setup = $installSchema->installCustomer($setup);
            $setup = $this->addBoletoInfoToTransactionTable($setup);
        }

        if (version_compare($version, "1.7.2", "<")) {
            $setup = $this->addStoreIdToConfigurationTable($setup);
            $setup = $this->addCardOwnerNameToCardsTable($setup);
        }

        if (version_compare($version, "1.8.1", "<")) {
            $setup = $this->addCreatedAtToCardsTable($setup);
        }

        if (version_compare($version, "1.8.7", "<")) {
            $setup = $this->addMetadataToChargeTable($setup);
            $setup = $this->addCustomerIdToChargeTable($setup);
            $setup = $this->addCardDataToTransactionTable($setup);
        }

        if (version_compare($version, "1.8.15", ">=")) {
            $setup = $installSchema->installProductsSubscription($setup);
            $setup = $installSchema->installSubscriptionRepetitions($setup);
            $setup = $installSchema->installRecurrenceSubscription($setup);
            $setup = $installSchema->installRecurrenceCharge($setup);
            $setup = $installSchema->installSubProducts($setup);
            $setup = $installSchema->installProductsPlan($setup);
        }

        if (version_compare($version, "2.0.1-beta", ">=")) {
            $setup = $this->addMundipaggIdToSubProductsTable($setup);
        }

        $setup->endSetup();
    }

    protected function updateVersionOneZeroTwo($setup)
    {
        $installer = $setup;
        $installer->startSetup();
 
        // Get tutorial_simplenews table
        $tableName = $installer->getTable('mundipagg_mundipagg_cards');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
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
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer Id'
                )
                ->addColumn(
                    'card_token',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Card Token'
                )
                ->addColumn(
                    'card_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Card Id'
                )
                ->addColumn(
                    'last_four_numbers',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Last Four Numbers'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Updated At'
                )
                ->setComment('Mundipagg Card Tokens')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
 
        $installer->endSetup();

        return $setup;
    }

    protected function updateVersionOneZeroTwelve($setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable('mundipagg_mundipagg_cards'),
            'brand',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'default' => '',
                'comment' => 'Card Brand'
            ]
        );

        $installer->endSetup();

        return $setup;
    }

    protected function fixTransactionTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();

        $connection->modifyColumn(
            $installer->getTable('mundipagg_module_core_transaction'),
            'acquirer_tid',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 300,
            ]
        )
        ->modifyColumn(
            $installer->getTable('mundipagg_module_core_transaction'),
            'acquirer_nsu',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 300,
            ]
        )
        ->modifyColumn(
            $installer->getTable('mundipagg_module_core_transaction'),
            'acquirer_auth_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 300,
            ]
        );

        return $setup;
    }

    protected function addBoletoInfoToTransactionTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_transaction');

        $connection->addColumn(
            $tableName,
            'boleto_url',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 500,
                'nullable' => true,
                'comment' => 'Boleto url'
            ]
        );

        return $setup;
    }

    protected function addStoreIdToConfigurationTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_configuration');

        $connection->addColumn(
            $tableName,
            'store_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'Store id'
            ]
        );

        return $setup;
    }

    protected function addCardOwnerNameToCardsTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_saved_card');

        $connection->addColumn(
            $tableName,
            'owner_name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'Card owner name'
            ]
        );

        return $setup;
    }

    protected function addCreatedAtToCardsTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_saved_card');

        $connection->addColumn(
            $tableName,
            'created_at',
            [
                'type' => Table::TYPE_DATETIME,
                'nullable' => false,
                'comment' => 'Card createdAt'
            ]
        );

        return $setup;
    }

    protected function addMetadataToChargeTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_charge');

        $connection->addColumn(
            $tableName,
            'metadata',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 500,
                'nullable' => true,
                'comment' => 'Charge metadata'
            ]
        );

        return $setup;
    }

    protected function addCustomerIdToChargeTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_charge');

        $connection->addColumn(
            $tableName,
            'customer_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'Charge customer id'
            ]
        );

        return $setup;
    }

    protected function addCardDataToTransactionTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_transaction');

        $connection->addColumn(
            $tableName,
            'card_data',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 600,
                'nullable' => true,
                'comment' => 'Card data'
            ]
        );

        return $setup;
    }

    protected function addMundipaggIdToSubProductsTable($setup)
    {
        $installer = $setup;

        $connection = $installer->getConnection();
        $tableName = $installer->getTable('mundipagg_module_core_recurrence_sub_products');

        $connection->addColumn(
            $tableName,
            'mundipagg_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 21,
                'nullable' => true,
                'comment' => 'Mundipagg Id'
            ]
        );

        return $setup;
    }

}