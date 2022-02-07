<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SearchRecipient extends Field
{

    protected function _renderValue(AbstractElement $element): string
    {
        $fieldId = $element->getHtmlId();
        $fieldName = $element->getName();
        $fieldValue = $element->getEscapedValue();
        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);

        $html .= '<input type="text" class="input-text admin__control-text" name="{{FIELD_NAME}}" id="{{FIELD_ID}}" placeholder="" value="{{FIELD_VALUE}}">';
        $html .= sprintf(
            '<button type="button" class="action-basic" api-url="%s" id="pagarme-get-info" data-action="">
                  <span>%s</span>
            </button>',
            $this->getSearchUrl(),
            __('Get info')
        );
        $html .= '<p>Pagar.me rerecipient id that represents your marketplace</p>';
        $html .= '</td>';

        return str_replace(['{{FIELD_ID}}', '{{FIELD_NAME}}', '{{FIELD_VALUE}}'], [$fieldId, $fieldName, $fieldValue], $html);
    }

    protected function getSearchUrl(): string
    {
        return $this->getBaseUrl() . 'rest/V1/pagarme/marketplace/recipient/searchRecipient';
    }


}
