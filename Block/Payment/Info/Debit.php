<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Magento\Payment\Block\Info\Cc;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;

class Debit extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/debit.phtml';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * @return string
     */
    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    /**
     * @return mixed
     */
    public function getCardBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getThreeDSStatus()
    {
        $authenticationAdditionalInformation = $this->getInfo()->getAdditionalInformation('authentication');
        if (empty($authenticationAdditionalInformation)) {
            return ''; 
        }
        
        $authentication = json_decode($authenticationAdditionalInformation, true);
        return AuthenticationStatusEnum::statusMessage(
            $authentication['trans_status'] ?? ''
        );
    }
}
