<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Magento\Payment\Block\Info;

class GooglePay extends Info
{
    const TEMPLATE = 'Pagarme_Pagarme::info/googlepay.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

}
