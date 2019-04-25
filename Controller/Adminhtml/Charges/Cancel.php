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
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Kernel\Services\LogService;
use MundiAPILib\Models\GetChargeResponse;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;

class Cancel extends ChargeAction
{
    /**
     * Cancel action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        parent::execute();
        $logService = new LogService(
            'Charge.Cancel',
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
            $logService->info("Amount or Chage not found");
            return $this->handlerFail("Amount or ChardId not found");
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
        $logService->info("Cancel charge on Mundipagg - " . $chargeId);
        $resultApi = $apiService->cancelCharge($charge, $amount);

        if ($resultApi === null) {

            $order->updateCharge($charge);

            $orderRepository->save($order);
            $history = $chargeHandlerService->prepareHistoryComment($charge);

            $order->getPlatformOrder()->addHistoryComment($history);
            $orderService->syncPlatformWith($order);

            $platformOrderGrandTotal = $moneyService->floatToCents(
                $platformOrder->getGrandTotal()
            );
            $platformOrderTotalCanceled = $moneyService->floatToCents(
                $platformOrder->getTotalCanceled()
            );

            $platformOrderTotalRefunded = $moneyService->floatToCents(
                $platformOrder->getTotalRefunded()
            );

            if (
                $platformOrderGrandTotal === $platformOrderTotalCanceled ||
                $platformOrderGrandTotal === $platformOrderTotalRefunded
            ) {
                $logService->info("Change Order status");

                $order->setStatus(OrderStatus::canceled());
                $order->getPlatformOrder()->setState(OrderState::canceled());
                $order->getPlatformOrder()->save();

                $order->getPlatformOrder()->addHistoryComment(
                    $i18n->getDashboard('Order canceled.')
                );

                $orderRepository->save($order);

                $orderService->syncPlatformWith($order);
            }

            $message = "Charge canceled with success";
            return $this->responseSuccess($message);
        }

        return $this->responseFail($resultApi);
    }
}