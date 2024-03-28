<?php

namespace Pagarme\Pagarme\Model;

class PagarmeGooglePay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "pagarmegooglepay";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}