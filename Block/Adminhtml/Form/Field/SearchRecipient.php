<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SearchRecipient extends Field
{

    protected function _renderValue(AbstractElement $element): string
    {
        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);
        $html .= '<p>Pagar.me recipient id that represents your marketplace</p>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= sprintf(
            '<button type="button" class="action-basic" api-url="%s" id="pagarme-get-info" data-action="">
                  <span>%s</span>
            </button>',
            $this->getSearchUrl(),
            __('Get info')
        );
        $html .= '</td>';

        return $html;
    }

    protected function getSearchUrl(): string
    {
        return $this->getBaseUrl() . 'rest/V1/pagarme/marketplace/recipient/searchRecipient';
    }


}
