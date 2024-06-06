<?php
/**
 * Class Billet
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Payment\Info;

use Pagarme\Pagarme\Block\Payment\Info\BaseCardInfo;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;

class CreditCard extends BaseCardInfo
{
    const TEMPLATE = 'Pagarme_Pagarme::info/card.phtml';

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
    public function getCcType()
    {
        return $this->getCcTypeName();
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
