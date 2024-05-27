<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Pagarme\Pagarme\Block\Payment\Info\BaseCardInfo;

class Debit extends BaseCardInfo
{
    const TEMPLATE = 'Pagarme_Pagarme::info/debitCard.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }
    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }
}
