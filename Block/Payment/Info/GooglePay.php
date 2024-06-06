<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Pagarme\Pagarme\Block\Payment\Info\BaseCardInfo;

class GooglePay extends BaseCardInfo
{
    const TEMPLATE = 'Pagarme_Pagarme::info/card.phtml';
    
    public function getTitle()
    {
        return "Google Pay";
    }
    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }
}
