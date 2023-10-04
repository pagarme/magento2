<?php

namespace Pagarme\Pagarme\Service\Order;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;

class ChargeService
{
    /**
     * @param mixed $incrementId
     * @return array
     * @throws InvalidParamException
     */
    public function findChargesByIncrementId($incrementId)
    {

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByPlatformId($incrementId);

        if ($order === null) {
            return [];
        }

        $chargeRepository = new ChargeRepository();
        return $chargeRepository->findByOrderId(
            new OrderId($order->getPagarmeId()->getValue())
        );
    }
}
