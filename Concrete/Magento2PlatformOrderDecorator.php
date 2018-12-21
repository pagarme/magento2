<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Mundipagg\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

class Magento2PlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var Order */
    protected $platformOrder;
    /**
     * @var Order
     */
    private $orderFactory;

    public function __construct()
    {
        $objectManager = ObjectManager::getInstance();
        $this->orderFactory = $objectManager->get('Magento\Sales\Model\Order');
    }

    public function save()
    {
        /*
         * @fixme Saving order this way in magento2 is deprecated.
         *        Find out how to fix this.
         */
        $this->platformOrder->save();
    }

    public function setState(OrderState $state)
    {
       $stringState = $state->getState();
       $this->platformOrder->setState($stringState);
    }

    public function setStatus(OrderStatus $status)
    {
        $stringStatus = $status->getStatus();
        $this->platformOrder->setStatus($stringStatus);
    }

    public function loadByIncrementId($incrementId)
    {
        $this->platformOrder =
            $this->orderFactory->loadByIncrementId($incrementId);
    }

    protected function addMPHistoryComment($message)
    {
        $this->platformOrder->addCommentToStatusHistory($message);
    }

    public function setIsCustomerNotified()
    {
        // TODO: Implement setIsCustomerNotified() method.
    }

    public function canInvoice()
    {
        return $this->platformOrder->canInvoice();
    }


    protected function setOrderStates()
    {
        // TODO: Implement setOrderStates() method.
    }

    public function getIncrementId()
    {
        return $this->getPlatformOrder()->getIncrementId();
    }
}