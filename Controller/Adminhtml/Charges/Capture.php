<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Charges;

use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Services\ChargeService;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;

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
        $params = $this->request->getParams();

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            $logService->info("Amount or Charge not found");
            return $this->handlerFail("Amount or Charge not found");
        }

        $amount = str_replace([',', '.'], "", $params['amount']);
        $chargeId = $params['chargeId'];

        $chargeRepository = new ChargeRepository();
        $charge = $chargeRepository->findByMundipaggId(
            new ChargeId($chargeId)
        );

        $chargeService = new ChargeService($charge);
        $response = $chargeService->capture($amount);
        if ($response->isSuccess()) {
            return $this->responseSuccess($response->getMessage());
        }
        return $this->responseFail($response->getMessage());
    }
}