<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;

class Magento2DataService extends AbstractDataService
{
    public function updateAcquirerData(Order $order)
    {
        $platformOrder = $order->getPlatformOrder()->getPlatformOrder();

        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get('Magento\Sales\Model\Order\Payment\Transaction\Repository');
        $lastTransId = $platformOrder->getPayment()->getLastTransId();
        $paymentId = $platformOrder->getPayment()->getEntityId();
        $orderId = $platformOrder->getPayment()->getParentId();

        $transactionAuth = $transactionRepository->getByTransactionId(
            str_replace('-capture', '', $lastTransId),
            $paymentId,
            $orderId
        );

        $transactionCapture = $transactionRepository->getByTransactionId(
            $lastTransId,
            $paymentId,
            $orderId
        );

        //to prevent overwriting auth transaction
        if ($transactionAuth->getTxnId() === $transactionCapture->getTxnId())
        {
            return;
        }

        if ($transactionAuth !== false) {
            $currentCharges = $order->getCharges();

            foreach($currentCharges as $charge) {
                $baseKey = $this->getChargeBaseKey($transactionAuth, $charge);
                if ($baseKey === null) {
                    continue;
                }

                $lastMundipaggTransaction = $charge->getLastTransaction();

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_acquirer_nsu',
                    $lastMundipaggTransaction->getAcquirerNsu()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_acquirer_tid',
                    $lastMundipaggTransaction->getAcquirerTid()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_acquirer_auth_code',
                    $lastMundipaggTransaction->getAcquirerAuthCode()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_acquirer_name',
                    $lastMundipaggTransaction->getAcquirerName()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_acquirer_message',
                    $lastMundipaggTransaction->getAcquirerMessage()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_brand',
                    $lastMundipaggTransaction->getBrand()
                );

                $transactionCapture->setAdditionalInformation(
                    $baseKey . '_installments',
                    $lastMundipaggTransaction->getInstallments()
                );
            }

            $transactionCapture->save();
        }
    }

    private function getChargeBaseKey($transactionAuth, $charge)
    {
        $orderCreationResponse =
            $transactionAuth->getAdditionalInformation('mundipagg_payment_module_api_response');

        if ($orderCreationResponse === null) {
            return null;
        }

        $orderCreationResponse = json_decode($orderCreationResponse);

        $authCharges = $orderCreationResponse->charges;

        $outdatedCharge = null;
        foreach ($authCharges as $authCharge) {
            if ($charge->getMundipaggId()->equals(new ChargeId($authCharge->id)))
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
}