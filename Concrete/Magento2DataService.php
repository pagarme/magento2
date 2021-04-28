<?php

namespace Pagarme\Pagarme\Concrete;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Pagarme\Core\Kernel\Abstractions\AbstractDataService;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;

class Magento2DataService extends AbstractDataService
{
    public function updateAcquirerData(Order $order)
    {
        $platformOrder = $order->getPlatformOrder()->getPlatformOrder();

        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get(Repository::class);
        $lastTransId = $platformOrder->getPayment()->getLastTransId();
        $paymentId = $platformOrder->getPayment()->getEntityId();
        $orderId = $platformOrder->getPayment()->getParentId();

        $transactionAuth = $transactionRepository->getByTransactionId(
            str_replace('-capture', '', $lastTransId),
            $paymentId,
            $orderId
        );

        if ($transactionAuth === false) {
            $transactionAuth =
                $transactionRepository->getByTransactionId(
                    $lastTransId,
                    $paymentId,
                    $orderId
                );
        }

        $additionalInfo = [];
        if ($transactionAuth !== false) {
            $currentCharges = $order->getCharges();

            foreach($currentCharges as $charge) {
                $baseKey = $this->getChargeBaseKey($transactionAuth, $charge);
                if ($baseKey === null) {
                    continue;
                }

                $lastPagarmeTransaction = $charge->getLastTransaction();

                $additionalInfo[$baseKey . '_acquirer_nsu'] =
                    $lastPagarmeTransaction->getAcquirerNsu();

                $additionalInfo[$baseKey . '_acquirer_tid'] =
                    $lastPagarmeTransaction->getAcquirerTid();

                $additionalInfo[$baseKey . '_acquirer_auth_code'] =
                    $lastPagarmeTransaction->getAcquirerAuthCode();

                $additionalInfo[$baseKey . '_acquirer_name'] =
                    $lastPagarmeTransaction->getAcquirerName();

                $additionalInfo[$baseKey . '_acquirer_message'] =
                    $lastPagarmeTransaction->getAcquirerMessage();

                $additionalInfo[$baseKey . '_brand'] =
                    $lastPagarmeTransaction->getBrand();

                $additionalInfo[$baseKey . '_installments'] =
                    $lastPagarmeTransaction->getInstallments();
            }

            $this->OLDcreateCaptureTransaction(
                $platformOrder,
                $transactionAuth,
                $additionalInfo
            );
        }
    }

    private function getChargeBaseKey($transactionAuth, $charge)
    {
        $orderCreationResponse =
            $transactionAuth->getAdditionalInformation('pagarme_payment_module_api_response');

        if ($orderCreationResponse === null) {
            return null;
        }

        $orderCreationResponse = json_decode($orderCreationResponse);

        $authCharges = $orderCreationResponse->charges;

        $outdatedCharge = null;
        foreach ($authCharges as $authCharge) {
            if ($charge->getPagarmeId()->equals(new ChargeId($authCharge->id)))
            {
                $outdatedCharge = $authCharge;
            }
        }

        if ($outdatedCharge === null) {
            return null;
        }

        try {
            //if it have no nsu, then it isn't a credit_card transaction;
            $lastNsu = $outdatedCharge->last_transaction->acquirer_nsu;
        }catch (\Throwable $e) {
            return null;
        }

        $additionalInformation = $transactionAuth->getAdditionalInformation();
        foreach ($additionalInformation as $key => $value) {
            if ($value == $lastNsu) {
                return str_replace('_acquirer_nsu', '', $key);
            }
        }

        return null;
    }

    /** This method is to mantain compatibility with legacy orders */
    private function OLDcreateCaptureTransaction($order, $transactionAuth, $additionalInformation)
    {
        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get(Repository::class);

        /** @var Order\Payment $payment */
        $payment = $order->getPayment();

        $transaction = $transactionRepository->create();
        $transaction->setParentId($transactionAuth->getTransactionId());
        $transaction->setOrderId($order->getEntityId());
        $transaction->setPaymentId($payment->getEntityId());
        $transaction->setTxnId($transactionAuth->getTxnId() . '-capture');
        $transaction->setParentTxnId($transactionAuth->getTxnId(), $transactionAuth->getTxnId() . '-capture');
        $transaction->setTxnType('capture');
        $transaction->setIsClosed(true);

        foreach ( $additionalInformation as $key => $value ) {
            $transaction->setAdditionalInformation($key, $value);
        }

        $transactionRepository->save($transaction);
    }

    public function createCaptureTransaction(Order $order)
    {
        $this->createTransaction($order, parent::TRANSACTION_TYPE_CAPTURE);
    }

    public function createAuthorizationTransaction(Order $order)
    {
        $this->createTransaction($order, parent::TRANSACTION_TYPE_AUTHORIZATION);
    }

    private function createTransaction(Order $order, $transactionType)
    {
        $platformOrder = $order->getPlatformOrder()->getPlatformOrder();
        $platformPayment = $platformOrder->getPayment();

        $objectManager = ObjectManager::getInstance();

        /**
         * @var Repository $transactionRepository
         */
        $transactionRepository = $objectManager->get(Repository::class);

        $transaction = $transactionRepository->create();

        $transaction->setOrderId($platformOrder->getEntityId());
        $transaction->setPaymentId($platformPayment->getEntityId());
        $transaction->setTxnType($transactionType);
        $transaction->setIsClosed(true);
        $transaction->setTxnId($order->getPagarmeId()->getValue() . '-' . $transactionType);

        $charges = $order->getCharges();
        $additionalInformation = [];
        foreach ($charges as $charge) {
            //@todo verify behavior for other types of charge, like boleto.
            $chargeId = $charge->getPagarmeId()->getValue();
            $lastTransaction = $charge->getLastTransaction();

            $additionalInformation[$chargeId] = [];

            $additionalInformation[$chargeId]['acquirer_nsu'] =
                $lastTransaction->getAcquirerNsu();

            $additionalInformation[$chargeId]['acquirer_tid'] =
                $lastTransaction->getAcquirerTid();

            $additionalInformation[$chargeId]['acquirer_auth_code'] =
                $lastTransaction->getAcquirerAuthCode();

            $additionalInformation[$chargeId]['acquirer_name'] =
                $lastTransaction->getAcquirerName();

            $additionalInformation[$chargeId]['acquirer_message'] =
                $lastTransaction->getAcquirerMessage();

            $additionalInformation[$chargeId]['brand'] =
                $lastTransaction->getBrand();

            $additionalInformation[$chargeId]['installments'] =
                $lastTransaction->getInstallments();
        }

        foreach ($additionalInformation as $key => $value) {
            $transaction->setAdditionalInformation($key, $value);
        }

        try {
            $transactionRepository->save($transaction);
        } catch (\Exception $e) {
            // nothing
        }

        $platformPayment->setLastTransId($transaction->getTxnId());
        $platformPayment->save();
    }
}
