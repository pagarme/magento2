<?php

namespace Pagarme\Pagarme\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class DayPeriod
 */
class CycleDiscount extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('cycle', ['label' => __('Cycle')]);
        $this->addColumn('discount', ['label' => __('(%)')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('+');
    }
}
