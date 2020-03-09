<?php

namespace MundiPagg\MundiPagg\Block\Payment\Info;

use Magento\Payment\Block\Info\Cc;

class Voucher extends Cc
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/creditCard.phtml'; //@todo

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    public function getCcType()
    {
        return $this->getCcTypeName();
    }

    public function getCardNumber()
    {
        return '**** **** **** ' . $this->getInfo()->getCcLast4();
    }

    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }
}