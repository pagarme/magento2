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

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info\Cc;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class TwoCreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/twoCreditCard.phtml';

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

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    public function getInstallmentsFirstCard()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments_first');
    }

    public function getCcTypeFirst()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type_first');
    }

    public function getFirstCardAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_first_card_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_first_card_tax_amount');
    }

    public function getFirstCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4_first');
    }

    public function getInstallmentsSecondCard()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments_second');
    }

    public function getCcTypeSecond()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type_second');
    }

    public function getSecondCardAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_second_card_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_second_card_tax_amount');
    }

    public function getSecondCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4_second');
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws InvalidParamException
     */
    public function getTransactionInfo()
    {
        Magento2CoreSetup::bootstrap();
        $orderService = new OrderService();

        $orderEntityId = $this->getInfo()->getOrder()->getIncrementId();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();

        if ($orderPagarmeId === null) {
            return [];
        }

        /**
         * @var Order orderObject
         */
        $orderObject = $orderService->getOrderByPagarmeId(new OrderId($orderPagarmeId));

        return [
            'card1' => array_merge(
                $orderObject->getCharges()[0]->getAcquirerTidCapturedAndAutorize(),
                ['tid' => $this->getTid($orderObject->getCharges()[0])]
            ),

            'card2' => array_merge(
                $orderObject->getCharges()[1]->getAcquirerTidCapturedAndAutorize(),
                ['tid' => $this->getTid($orderObject->getCharges()[1])]
            )
        ];
    }

    private function getTid(Charge $charge)
    {
        $transaction = $charge->getLastTransaction();

        $tid = null;
        if ($transaction !== null) {
            $tid = $transaction->getAcquirerTid();
        }

        return $tid;
    }
}
