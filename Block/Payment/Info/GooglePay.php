<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Pagarme\Pagarme\Block\Payment\Info\BaseCardInfo;

class GooglePay extends BaseCardInfo
{
    const TEMPLATE = 'Pagarme_Pagarme::info/googlepay.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }
}
