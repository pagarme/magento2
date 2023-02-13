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
use Pagarme\Pagarme\Helper\Payment\Pix as PixHelper;

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
        $pixHelper = new PixHelper();
        return $pixHelper->getQrCode($this->getInfo());
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

        if ($orderObject === null) {
            return [];
        }

        return $orderObject->getCharges()[0]->getLastTransaction();
    }
}
