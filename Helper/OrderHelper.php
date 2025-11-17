<?php

namespace Pagarme\Pagarme\Helper;

use Pagarme\Core\Kernel\Repositories\OrderRepository;

class OrderHelper
{
    public static function getPagarmeIdByIncrementId($incrementId)
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByPlatformId($incrementId);
        if (is_null($order)) {
            return null;
        }
        return $order->getPagarmeId()->getValue();
    }

    public static function getPagarmeOrderId($info)
    {
        $orderId = $info->getOrder()->getIncrementId();
        $lastTransId = $info->getLastTransId();
        if (preg_match('/^or_/', $lastTransId ?? '') === false) {
            return OrderHelper::getPagarmeIdByIncrementId($orderId);
        }
        return substr($lastTransId, 0, 19);
    }
}
