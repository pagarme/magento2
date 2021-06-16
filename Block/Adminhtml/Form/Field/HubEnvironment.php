<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class HubEnvironment extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element)
    {
        Magento2CoreSetup::bootstrap();
        $config = Magento2CoreSetup::getModuleConfiguration();
        $environment = $config->getHubEnvironment();

        return '<td class="value">' . $environment . '</td>';
    }

}
