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
use Pagarme\Pagarme\Model\Enum\TdsReasonEnum;
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

    public function getTransactionInfo()
    {
        $info = parent::getTransactionInfo();
        if (empty($info)) {
            return $info;
        }
        return array_merge($info, [
            '3dsStatus' => $this->getThreeDSStatus(),
            '3dsReason' => $this->getTdsReasonLabel(),
        ]);
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

    public function getTdsReasonLabel()
    {
        $reason = $this->getInfo()->getAdditionalInformation('3ds_reason');
        $labels = [
            TdsReasonEnum::NOT_ELIGIBLE        => 'Not eligible for 3DS',
            TdsReasonEnum::CONFIG_DISABLED     => '3DS disabled in configuration',
            TdsReasonEnum::AMOUNT_BELOW_MIN    => 'Amount below minimum for 3DS',
            TdsReasonEnum::BRAND_NOT_SUPPORTED => 'Brand not supported for 3DS',
            TdsReasonEnum::AUTHORIZED          => 'Authorized via 3DS',
            TdsReasonEnum::DECLINED            => 'Declined via 3DS',
            TdsReasonEnum::UNKNOWN             => 'Unknown reason',
        ];
        return $labels[$reason] ?? '';
    }
}
