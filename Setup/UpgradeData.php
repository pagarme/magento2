<?php


namespace MundiPagg\MundiPagg\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Customer\Setup\CustomerSetupFactory;


use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;
    private $eavSetupFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
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

    public function updateConfigProductPlanPaymment($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_payment',
            [
                'group' => 'Plan Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 1,
                'label' => 'Payment Methods',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavPaymentMethods',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'credit',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'plan'
            ]
        );
    }

    public function updateConfigProductPlanInterval($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_interval',
            [
                'group' => 'Plan Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 2,
                'label' => 'Interval',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavInterval',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'day',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'plan,recurrence'
            ]
        );

    }

    public function updateConfigProductPlanIntervalPeriod($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_interval_period',
            [
                'group' => 'Plan Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 3,
                'label' => 'Period',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'plan,recurrence'
            ]
        );
    }

    public function updateConfigProductPlanBillingType($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_billing_type',
            [
                'group' => 'Plan Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 4,
                'label' => 'Billing Type',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavBillingType',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'day',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'plan,recurrence'
            ]
        );
    }

    public function updateConfigProductPlanBillingPeriodDays($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_billing_type_day',
            [
                'group' => 'Plan Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 5,
                'label' => 'Day(s)',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'plan'
            ]
        );
    }

    public function updateConfigProductPlanTrialPeriod($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_trial_period',
            [
                'group' => 'Plan Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 6,
                'label' => 'Trial Period',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'plan'
            ]
        );
    }

    public function updateConfigProductRecurrencePaymment($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_rec_interval'
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundipagg_rec_payment'
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_payment',
            [
                'group' => 'Recurrence Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 4,
                'label' => 'Payment Methods',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavPaymentMethods',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'credit',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceInterval($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_interval',
            [
                'group' => 'Recurrence Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 5,
                'label' => 'Interval',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavInterval',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'day',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceIntervalPeriod($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_interval_period',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 6,
                'label' => 'Period',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_fHront' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceBillingType($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_billing_type',
            [
                'group' => 'Recurrence Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 7,
                'label' => 'Billing Type',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavBillingType',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'day',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceBillingPeriodDays($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_billing_type_day',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 8,
                'label' => 'Day(s)',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceStartAt($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_start_at',
            [
                'group' => 'Recurrence Config',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 9,
                'label' => 'Start At',
                'input' => 'select',
                'class' => '',
                'source' => 'MundiPagg\MundiPagg\Model\Source\EavInterval',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 'day',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'option' => [
                    'values' => [],
                ],
                'apply_to'=>'recurrence'
            ]
        );

    }

    public function updateConfigProductRecurrenceStartAtDays($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_start_at_day',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 10,
                'label' => 'Day(s)',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceCycleDiscount($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_cycle_discount');
    }

    public function updateConfigProductRecurrenceAllowSet($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_allow_set',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 1,
                'label' => 'Allow sell as normal product',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceGlobal($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_global',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 2,
                'label' => 'Use Global Configuration',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }

    public function updateConfigProductRecurrenceAllowOther($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_allow_other',
            [
                'group' => 'Recurrence Config',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 3,
                'label' => 'Allow to add with other recurrence items in the cart',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'=>'recurrence'
            ]
        );
    }
}