<?php

namespace MundiPagg\MundiPagg\Block\Payment\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformOrderDecorator;

class Pix extends Info
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/pix.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getPixUrl()
    {
        $method = $this->getInfo()->getMethod();

        if (strpos($method, "mundipagg_pix") === false) {
            return null;
        }

        return 'pix-url-qrcode';
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

        $orderMundipaggId = $platformOrder->getMundipaggId();

        if ($orderMundipaggId === null) {
            return [];
        }

        /**
         * @var Order orderObject
         */
        $orderObject = $orderService->getOrderByMundiPaggId(new OrderId($orderMundipaggId));
        return $orderObject->getCharges()[0]->getLastTransaction();
    }
}
