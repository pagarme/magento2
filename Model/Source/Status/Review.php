<?php

namespace Pagarme\Pagarme\Model\Source\Status;


use Magento\Sales\Model\Config\Source\Order\Status;

class Review extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
    ];
}
