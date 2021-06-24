<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Charges;

use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;

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
        $params = $this->request->getParams();
        $logService = new LogService(
            'ChargeService',
            true
        );

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            $error = "Amount or ChargeID not found";
            $logService->info($error);
            return $this->responseFail($error);
        }

        $this->setWebsiteConfiguration($params['chargeId']);

        $amount = str_replace([',', '.'], "", $params['amount']);
        $chargeId = $params['chargeId'];

        $chargeService = new ChargeService();
        $logService->info("Canceling charge {$chargeId} by Magento admin");
        $response = $chargeService->cancelById($chargeId, $amount);

        if ($response->isSuccess()) {
            return $this->responseSuccess($response->getMessage());
        }
        return $this->responseFail($response->getMessage());
    }
}
