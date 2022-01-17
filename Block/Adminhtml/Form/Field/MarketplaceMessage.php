<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\ObjectManager;

class MarketplaceMessage extends Fieldset
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = "";
        $objectManager = ObjectManager::getInstance();
        $moduleManager = $objectManager->get("\Magento\Framework\Module\Manager");
        if(!$moduleManager->isEnabled("Webkul_Marketplace")){
            $html = '<td class="value">';
            $html .= '<strong class="colorRed">Aviso!</strong>';
            $html .= ' Você precisa ter o módulo';
            $html .= ' <a href="https://store.webkul.com/magento2-multi-vendor-marketplace.html" target="_blank">';
            $html .= ' Webkul Marketplace';
            $html .= '</a>';
            $html .= ' ativo.</td>';
        }
        return $html;
    }

}
