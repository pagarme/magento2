<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Marketplace;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Recipient extends Template
{
    /**
     * Link constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ){
        parent::__construct($context, []);
    }
}
