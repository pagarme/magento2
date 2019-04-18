<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Charges;

use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

use Magento\Framework\App\Request\Http;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

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

class Capture extends ChargeAction
{

    /**
     * Capture action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $params = $this->request->getParams();
        $code = 200;
        $message = "";

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            return $this->handlerFail("Amount or ChardId not found");
        }

        $amount = $params['amount'];
        $chargeId = $params['chargeId'];
        $orderId = $params['orderId'];

        $order = $orderRepository->findByMundipaggId($orderId);

        $charge = $chargeRepository->findByMundipaggId(
            new ChargeId($chargeId)
        );

        $paidAmount = $transaction->getPaidAmount();
        if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
            $charge->pay($amount);
        }

        if ($charge->getPaidAmount() == 0) {
            $charge->setPaidAmount($paidAmount);
        }

        $apiService = new APIService();
        $resultApi = $apiService->captureCharge($charge, $amount);

        if (($resultApi instanceof \MundiAPILib\Models\GetChargeResponse) === false) {
            $code = 400;
            $message = $resultApi;
        }

        if ($code === 200) {

            $order->updateCharge($charge);

            $orderRepository->save($order);
            $orderService->syncPlatformWith($order);

            $chargeRepository->save($charge);
            $message = "Charge captured with success";

            return $this->responseSuccess($message);
        }
        return $this->responseFail($message);

    }
}