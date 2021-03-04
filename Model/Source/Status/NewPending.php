<?php

namespace Pagarme\Pagarme\Model\Source\Status;


use Magento\Sales\Model\Config\Source\Order\Status;

class NewPending extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
    ];
}
