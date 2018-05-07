<?php
namespace MundiPagg\MundiPagg\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class Recurrence {

    private $eavSetupFactory;

    public function setFactory($eavSetupFactory){
        $this->$eavSetupFactory = $eavSetupFactory;
        return $this->$eavSetupFactory;
    }

    public function recorrence($setup,$eavSetupFactory){

        $this->setFactory($eavSetupFactory);
        //Plan
        $this->updateConfigProductPlanPaymment($setup);
        $this->updateConfigProductPlanInterval($setup);
        $this->updateConfigProductPlanIntervalPeriod($setup);
        $this->updateConfigProductPlanBillingType($setup);
        $this->updateConfigProductPlanBillingPeriodDays($setup);
        $this->updateConfigProductPlanTrialPeriod($setup);

        // Recorrence
        $this->updateConfigProductRecurrencePaymment($setup);
        $this->updateConfigProductRecurrenceInterval($setup);
        $this->updateConfigProductRecurrenceIntervalPeriod($setup);
        $this->updateConfigProductRecurrenceBillingType($setup);
        $this->updateConfigProductRecurrenceBillingPeriodDays($setup);
        $this->updateConfigProductRecurrenceStartAt($setup);
        $this->updateConfigProductRecurrenceStartAtDays($setup);
        $this->updateConfigProductRecurrenceAllowSet($setup);
        $this->updateConfigProductRecurrenceGlobal($setup);
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
        /*
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'eav_mundi_rec_cycle_discount',
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
        */
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
