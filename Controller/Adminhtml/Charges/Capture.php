<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Charges;

use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Services\ChargeService;
use Mundipagg\Core\Kernel\Services\LogService;
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

        $logService = new LogService(
            'Capture on module',
            true
        );

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            $error = "Amount or ChargeID not found";
            $logService->info($error);
            return $this->responseFail($error);
        }

        $amount = str_replace([',', '.'], "", $params['amount']);
        $chargeId = $params['chargeId'];

        $chargeService = new ChargeService();
        $response = $chargeService->captureById($chargeId, $amount);

        if ($response->isSuccess()) {
            return $this->responseSuccess($response->getMessage());
        }
        return $this->responseFail($response->getMessage());
    }
}