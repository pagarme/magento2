<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class HubIntegration extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Exception
     */
    protected function _renderValue(AbstractElement $element)
    {

        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);
        $html .= '<p id="botao-hub"></p>';
        $html .= '</td>';

        return $html;
    }
}
