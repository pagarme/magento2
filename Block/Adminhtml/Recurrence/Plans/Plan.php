<?php

namespace MundiPagg\MundiPagg\Block\Adminhtml\Recurrence\Plans;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Plan extends Template
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