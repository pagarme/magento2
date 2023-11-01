<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Exception;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class EnableAdvanceSettings extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     * @throws Exception
     */
    public function render(AbstractElement $element)
    {
        Magento2CoreSetup::bootstrap();
        $config = Magento2CoreSetup::getModuleConfiguration();
        if (!empty($config->getAccountId())) {
            return '';
        }
        return parent::render($element);
    }
}
