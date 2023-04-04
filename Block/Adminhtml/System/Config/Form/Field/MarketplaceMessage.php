<?php

namespace Pagarme\Pagarme\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager as ModuleManager;

class MarketplaceMessage extends Field
{
    /**
        * @var ModuleManager
     */
    protected $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = "";
        if(!$this->moduleManager->isEnabled("Webkul_Marketplace")){
            $html = __(<<<MSG
                    <p class='message message-notification'>
                        You need to activate the 
                            <a href='https://store.webkul.com/magento2-multi-vendor-marketplace.html' target='_blank'>
                                Webkul Marketplace
                            </a>
                        extension.
                    </p>
                MSG);    
            }
        return $html;
    }

}
