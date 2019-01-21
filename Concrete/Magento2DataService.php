<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Aggregates\Order;

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
        $transaction = $transactionRepository->getByTransactionId(
            $lastTransId,
            $paymentId,
            $orderId
        );
        if ($transaction !== false) {

            $orderCreationResponse =
                $transaction->getAdditionalInformation('mundipagg_payment_module_api_response');

            if ($orderCreationResponse === null) {
                return;
            }

            $orderCreationResponse = json_decode($orderCreationResponse);

            $responseCharges = $orderCreationResponse->charges;
            $currentCharges = $order->getCharges();

            foreach($currentCharges as $charge) {
                $outdatedCharge = null;
                foreach ($responseCharges as $responseCharge)
                {
                    if ($responseCharge->id === $charge->getMundipaggId()->getValue()) {
                        $outdatedCharge = $responseCharge;
                    }
                }
                if ($outdatedCharge === null) {
                    continue;
                }

                $additionalInformation = $transaction->getAdditionalInformation();
                $lastTransaction = $charge->getLastTransaction();
                foreach ($additionalInformation as $key => $value) {
                    if (
                        strpos($key, 'acquirer_nsu') !== false &&
                        $value === $outdatedCharge->last_transaction->acquirer_nsu
                    ) {
                        $baseKey = str_replace('_acquirer_nsu', '', $key);
                        $transaction->setAdditionalInformation(
                            $baseKey . '_acquirer_nsu',
                            $lastTransaction->getAcquirerNsu()
                        );
                        //@fixme is it necessary update other info besides nsu?
                        /*$transaction->setAdditionalInformation(
                            $baseKey . '_acquirer_tid',
                            $lastTransaction->getAcquirerTid()
                        );*/
                        $transaction->save();
                        break;
                    }
                }
            }
        }
    }
}