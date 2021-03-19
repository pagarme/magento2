<?php

namespace Pagarme\Pagarme\Block\Payment\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class Pix extends Info
{
    const TEMPLATE = 'Pagarme_Pagarme::info/pix.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getPixInfo()
    {
        $info = $this->getInfo();
        $method = $info->getMethod();

        if (strpos($method, "pagarme_pix") === false) {
            return null;
        }

        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        Magento2CoreSetup::bootstrap();
        $orderService= new \Pagarme\Core\Payment\Services\OrderService();
        return $orderService->getPixQrCodeInfoFromOrder(new OrderId($orderId));
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    /**
     * @return mixed
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
        return $orderObject->getCharges()[0]->getLastTransaction();
    }
}
