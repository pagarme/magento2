<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Exception;
use Magento\Payment\Block\Info\Cc;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Magento\Framework\Exception\LocalizedException;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

abstract class BaseCardInfo extends Cc
{

    /**
     * @return array
     */
    public function getTransactionInfo()
    {
        $charge = $this->getLastCharge();

        if (empty($charge) || empty($charge->getLastTransaction())) {
            return [];
        }
        if ($charge->getLastTransaction()->getCardData() == null) {
            return [];
        }
        $lastFourDigitsWithDots = sprintf(
            "**** **** **** %s",
            $charge->getLastTransaction()->getCardData()->getLastFourDigits()->getValue()
        );
        return array_merge(
            $charge->getAcquirerTidCapturedAndAuthorize(),
            ['tid' => $charge->getLastTransaction()->getAcquirerTid() ?? ""],
            ['cardBrand' => $charge->getLastTransaction()->getCardData()->getBrand()->getName() ?? ""],
            ['installments' => $this->getInfo()->getAdditionalInformation('cc_installments') ?? ""],
            ['lastFour' => $lastFourDigitsWithDots],
            ['acquirerMessage' => $charge->getLastTransaction()->getAcquirerMessage() ?? ""]
        );
    }

    /**
     * @return mixed|array|Charge
     * @throws InvalidParamException
     * @throws LocalizedException
     * @throws Exception
     */
    private function getLastCharge()
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

        return current($orderObject->getCharges());
    }

    /**
     * @param mixed $orderService
     * @param mixed $pagarmeId
     * @return mixed
     */
    protected function getOrderObjectByPagarmeId($orderService, $pagarmeId)
    {
        $orderId = new OrderId($pagarmeId);
        return $orderService->getOrderByPagarmeId($orderId);
    }
    /**
     * @param mixed $incrementId
     * @return Magento2PlatformOrderDecorator
     */
    protected function loadPlatformOrderByIncrementId($incrementId)
    {
        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($incrementId);
        return $platformOrder;
    }
}
