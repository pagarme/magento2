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

            $platformOrder = $observer->getOrder();
            $transaction = $this->getTransaction($platformOrder);
            $orderService = new OrderService();
            if ($transaction !== false) {
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

            $incrementId = $platformOrder->getIncrementId();
            $platformOrder = new Magento2PlatformOrderDecorator();
            $platformOrder->loadByIncrementId($incrementId);
            $orderService->cancelAtMundipaggByPlatformOrder($platformOrder);
        } catch(AbstractMundipaggCoreException $e) {
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                $e->getCode()
            );
        }
    }

    private function getTransaction($order)
    {
        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get('Magento\Sales\Model\Order\Payment\Transaction\Repository');
        $lastTransId = $order->getPayment()->getLastTransId();
        $paymentId = $order->getPayment()->getEntityId();
        $orderId = $order->getPayment()->getParentId();
        return $transactionRepository->getByTransactionId(
            $lastTransId,
            $paymentId,
            $orderId
        );
    }
}
