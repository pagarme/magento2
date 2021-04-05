<?php

namespace Pagarme\Pagarme\Concrete;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Abstractions\AbstractCreditmemoDecorator;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;

class Magento2PlatformCreditmemoDecorator extends AbstractCreditmemoDecorator
{
    public function save()
    {
        if ($this->platformCreditmemo === null)
        {
            return;
        }

        $this->platformCreditmemo->save();
    }

    public function getIncrementId()
    {
        if ($this->platformCreditmemo === null)
        {
            return;
        }

        return $this->platformCreditmemo->getIncrementId();
    }

    /**
     * Based on \Magento\Sales\Model\Order\Payment::prepareCreditMemo()
     *
     * @see \Magento\Sales\Model\Order\Payment::prepareCreditMemo()
     *
     * @param PlatformOrderInterface $order
     */
    public function prepareFor(PlatformOrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order $platformOrder */
        $platformOrder = $order->getPlatformOrder();
        /** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
        $creditmemoFactory = ObjectManager::getInstance()->get('Magento\Sales\Model\Order\CreditmemoFactory');
        $creditMemo = $creditmemoFactory->createByOrder($platformOrder);

        if ($creditMemo) {
            $platformOrder->setShouldCloseParentTransaction(true);
        }

        $this->platformCreditmemo = $creditMemo;
    }

    public function refund()
    {
        if ($this->platformCreditmemo === null)
        {
            return;
        }

        /** @var \Magento\Sales\Model\Service\CreditmemoService $creditmemoService */
        $creditmemoService = ObjectManager::getInstance()
            ->get('Magento\Sales\Model\Service\CreditmemoService');
        $creditmemoService->refund($this->platformCreditmemo);
    }
}
