<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Charges;

use Mundipagg\Core\Kernel\Factories\TransactionFactory;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;

use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;

use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Webhook\Services\ChargeHandlerService;

use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Kernel\Services\LogService;
use MundiAPILib\Models\GetChargeResponse;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;

class Capture extends ChargeAction
{
    /**
     * Capture action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        parent::execute();
        $logService = new LogService(
            'Charge.Capture',
            true
        );

        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();
        $moneyService = new MoneyService();
        $chargeHandlerService = new ChargeHandlerService();
        $i18n = new LocalizationService();

        $params = $this->request->getParams();

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            $logService->info("Amount or Charge not found");
            return $this->handlerFail("Amount or Charge not found");
        }

        $amount = str_replace([',', '.'], "", $params['amount']);
        $chargeId = $params['chargeId'];
        $orderId = $params['orderId'];

        $order = $orderRepository->findByMundipaggId(
            new OrderId($orderId)
        );

        $platformOrder = $order->getPlatformOrder();

        $charge = $chargeRepository->findByMundipaggId(
            new ChargeId($chargeId)
        );

        $apiService = new APIService();
        $logService->info("Capturing charge on Mundipagg - " . $chargeId);
        $resultApi = $apiService->captureCharge($charge, $amount);

        if ($resultApi instanceof GetChargeResponse) {

            if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
                $logService->info("Pay charge - " . $chargeId);
                $charge->pay($amount);
            }

            if ($charge->getPaidAmount() == 0) {
                $charge->setPaidAmount($amount);
            }

            $logService->info("Update Charge on Order");
            $order->updateCharge($charge);
            $orderRepository->save($order);

            $logService->info("Adding history on Order");
            $history = $chargeHandlerService->prepareHistoryComment($charge);
            $platformOrder->addHistoryComment($history);


            $logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($order);

            $logService->info("Change Order status");
            $order->setStatus(OrderStatus::paid());
            $orderHandlerService = new OrderHandler();
            $cantCreateReason = $orderHandlerService->handle($order);

            $message = $chargeHandlerService->prepareReturnMessage($charge);

            return $this->responseSuccess($message);
        }

        $logService->info($resultApi);
        return $this->responseFail($resultApi);
    }
}