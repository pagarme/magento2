<?php


namespace MundiPagg\MundiPagg\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), "0.1.1", "<")) {
            $setup = $this->updateVersionZeroOneOne($setup);
        }

        if (version_compare($context->getVersion(), "0.1.2", "<")) {
            $setup = $this->updateVersionZeroOneTwo($setup);
        }

        if (version_compare($context->getVersion(), "1.2.15", "<")) {
            $setup = $this->updateVersionOneTwoFourteen($setup);
        }

        $setup->endSetup();
    }

    protected function updateVersionOneTwoFourteen($setup)
    {
        $setup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $attributeCode = 'customer_id_mundipagg';
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
        $customerSetup->addAttribute(
            'customer',
            $attributeCode, 
            [
                'label' => 'Customer Id Mundipagg',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'system'=> false,
                'position' => 200,
                'sort_order' => 200,
                'user_defined' => false,
                'default' => '0',
            ]
        );

        $eavConfig = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
        $eavConfig->setData('used_in_forms',['adminhtml_customer']);
        $eavConfig->save();

        $setup->endSetup();

        return $setup;
    }

    protected function updateVersionZeroOneTwo($setup)
    {
        $installer = $setup;
        $installer->startSetup();
 
        // Get tutorial_simplenews table
        $tableName = $installer->getTable('mundipagg_mundipagg_charges');
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
                    'charge_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Charge Id'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Code'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Order Id'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Status'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Amount'
                )
                ->addColumn(
                    'paid_amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Paid Amount'
                )
                ->addColumn(
                    'refunded_amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Refunded Amount'
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
                ->setComment('Mundipagg Charges')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
 
        $installer->endSetup();

        return $setup;
    }

    protected function updateVersionZeroOneOne($setup)
    {
        $tableName = $setup->getTable('sales_order_status_state');

        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();
            $where = ['state = ?' => 'pending_payment'];
            $data = ['visible_on_front' => 1];
            $connection->update($tableName, $data, $where);
        }

        return $setup;
    }

}
