<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Services\OrderService;
use Magento\Framework\Webapi\Exception as M2WebApiException;
use Magento\Framework\Phrase;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformOrderDecorator;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        try {
            Magento2CoreSetup::bootstrap();

            $platformOrder = $this->getPlatformOrderFromObserver($observer);
            if ($platformOrder === null) {
                return;
            }

            $transaction = $this->getTransaction($platformOrder);
            if ($transaction !== false) {
                $this->cancelOrderByTransactionInfo($transaction);
                return;
            }

            $incrementId = $platformOrder->getIncrementId();
            $this->cancelOrderByIncrementId($incrementId);
        } catch(AbstractMundipaggCoreException $e) {
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                $e->getCode()
            );
        }
    }

    private function cancelOrderByTransactionInfo($transaction)
    {
        $orderService = new OrderService();

        $orderCreationResponse =
            $transaction->getAdditionalInformation(
                'mundipagg_payment_module_api_response'
            );

        if ($orderCreationResponse !== null) {
            $orderCreationResponse = json_decode(
                $orderCreationResponse,
                true
            );

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPostData($orderCreationResponse);

            $orderService->cancelAtMundipagg($order);
            return;
        }
    }

    private function cancelOrderByIncrementId($incrementId)
    {
        $orderService = new OrderService();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($incrementId);
        $orderService->cancelAtMundipaggByPlatformOrder($platformOrder);
    }

    private function getPlatformOrderFromObserver(EventObserver $observer)
    {
        $platformOrder = $observer->getOrder();

        if ($platformOrder !== null)
        {
            return $platformOrder;
        }

        $payment = $observer->getPayment();
        if ($payment !== null) {
            return $payment->getOrder();
        }

        return null;
    }

    private function getTransaction($order)
    {
        $lastTransId = $order->getPayment()->getLastTransId();
        $paymentId = $order->getPayment()->getEntityId();
        $orderId = $order->getPayment()->getParentId();

        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get('Magento\Sales\Model\Order\Payment\Transaction\Repository');

        return $transactionRepository->getByTransactionId(
            $lastTransId,
            $paymentId,
            $orderId
        );
    }
}
