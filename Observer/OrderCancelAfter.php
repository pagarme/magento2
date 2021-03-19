<?php

namespace Pagarme\Pagarme\Observer;

use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\Services\OrderLogService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Magento\Framework\Webapi\Exception as M2WebApiException;
use Magento\Framework\Phrase;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     * @throws M2WebApiException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $event = $observer->getEvent();

        $order = $event->getOrder();
        if (empty($order)) {
            return $this;
        }

        $payment = $order->getPayment();

        if (!in_array($payment->getMethod(), $this->pagarmeMethods())) {
            return $this;
        }

        try {
            Magento2CoreSetup::bootstrap();

            $platformOrder = $this->getPlatformOrderFromObserver($observer);
            if ($platformOrder === null) {
                return false;
            }

            $transaction = $this->getTransaction($platformOrder);
            $chargeInfo = $this->getChargeInfo($transaction);

            if ($chargeInfo === false) {
                $this->cancelOrderByIncrementId($platformOrder->getIncrementId());
                return;
            }

            $this->cancelOrderByTransactionInfo(
                $transaction,
                $platformOrder->getIncrementId()
            );

        } catch(AbstractPagarmeCoreException $e) {
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                $e->getCode()
            );
        }
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $pagarmeProvider = $objectManager->get(PagarmeConfigProvider::class);

        return $pagarmeProvider->getModuleStatus();
    }

    private function cancelOrderByTransactionInfo($transaction, $orderId)
    {
        $orderService = new OrderService();

        $chargeInfo = $this->getChargeInfo($transaction);

        if ($chargeInfo !== false) {

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPostData($chargeInfo);

            $orderService->cancelAtPagarme($order);
            return;
        }

        $this->throwErrorMessage($orderId);
    }

    private function cancelOrderByIncrementId($incrementId)
    {
        $orderService = new OrderService();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($incrementId);
        $orderService->cancelAtPagarmeByPlatformOrder($platformOrder);
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

    private function getChargeInfo($transaction)
    {
        if ($transaction === false) {
            return false;
        }

        $chargeInfo =  $transaction->getAdditionalInformation();

        if (!empty($chargeInfo['pagarme_payment_module_api_response'])) {
            $chargeInfo =
                $chargeInfo['pagarme_payment_module_api_response'];
            return json_decode($chargeInfo,true);
        }

        return false;
    }

    private function throwErrorMessage($orderId)
    {
        $i18n = new LocalizationService();
        $message = "Can't cancel current order. Please cancel it by Pagar.me panel";

        $ExceptionMessage = $i18n->getDashboard($message);

        $e = new \Exception($ExceptionMessage);
        $log = new OrderLogService();
        $log->orderException($e, $orderId);

        throw $e;
    }

    private function pagarmeMethods()
    {
        return [
          'pagarme_creditcard',
          'pagarme_billet',
          'pagarme_two_creditcard',
          'pagarme_billet_creditcard',
        ];
    }
}
