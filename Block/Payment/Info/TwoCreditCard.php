<?php
/**
 * Class TwoCreditCard
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
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class TwoCreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/twoCreditCard.phtml';

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
     * @return string
     */
    public function getCardNumber()
    {
        return '**** **** **** ' . $this->getInfo()->getCcLast4();
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    /**
     * @return mixed
     */
    public function getInstallmentsFirstCard()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments_first');
    }

    /**
     * @return mixed
     */
    public function getCcTypeFirst()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type_first');
    }

    /**
     * @return float
     */
    public function getFirstCardAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_first_card_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_first_card_tax_amount');
    }

    /**
     * @return string
     */
    public function getFirstCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4_first');
    }

    /**
     * @return mixed
     */
    public function getInstallmentsSecondCard()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments_second');
    }

    /**
     * @return mixed
     */
    public function getCcTypeSecond()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type_second');
    }

    /**
     * @return float
     */
    public function getSecondCardAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_second_card_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_second_card_tax_amount');
    }

    /**
     * @return string
     */
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
        $platformOrder = $this->loadPlatformOrderByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();

        if ($orderPagarmeId === null) {
            return [];
        }

        $orderObject = $this->getOrderObjectByPagarmeId($orderService, $orderPagarmeId);

        if ($orderObject === null || !is_object($orderObject)) {
            return [];
        }

        $charges = $orderObject->getCharges();
        $chargeOne = current($charges);
        $chargeTwo = next($charges);

        return [
            'card1' => array_merge(
                $chargeOne->getAcquirerTidCapturedAndAutorize(),
                ['tid' => $this->getTid($chargeOne)]
            ),
            'card2' => array_merge(
                $chargeTwo->getAcquirerTidCapturedAndAutorize(),
                ['tid' => $this->getTid($chargeTwo)]
            )
        ];
    }

    /**
     * @param mixed $incrementId
     * @return Magento2PlatformOrderDecorator
     */
    private function loadPlatformOrderByIncrementId($incrementId)
    {
        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($incrementId);
        return $platformOrder;
    }

    /**
     * @param mixed $orderService
     * @param mixed $pagarmeId
     * @return mixed
     */
    private function getOrderObjectByPagarmeId($orderService, $pagarmeId)
    {
        $orderId = new OrderId($pagarmeId);
        return $orderService->getOrderByPagarmeId($orderId);
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
