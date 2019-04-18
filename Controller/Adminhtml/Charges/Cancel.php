<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Charges;

use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

use Magento\Framework\App\Request\Http;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;

class Cancel extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Http $request
    ) {

        Magento2CoreSetup::bootstrap();

        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Cancel action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->request->getParams();
        $code = 200;
        $message = "";

        if (!isset($params['amount']) || !isset($params['chargeId'])) {
            return $this->handlerFail("Amount or ChardId not found");
        }

        $amount = $params['amount'];
        $chargeId = $params['chargeId'];

        $charge = (new ChargeRepository)->findByMundipaggId(
            new ChargeId($chargeId)
        );

        $charge->cancel($amount);
        $apiService = new APIService();
        $resultApi = $apiService->cancelCharge($charge);

        if ($resultApi !== null) {
            $code = 400;
            $message = $resultApi;
        }

        (new ChargeRepository)->save($charge);

        $message = "Charge canceled with success";

        if ($code === 200) {
            return $this->responseSuccess($message);
        }
        return $this->responseFail($message);

    }

    public function responseSuccess($message)
    {
        return $this->handleResult(200, $message);
    }

    public function responseFail($message)
    {
        return $this->handleResult(400, $message);
    }

    public function handleResult($code, $message)
    {
        $result = $this->resultJsonFactory;
        $json = $result->create();
        $json->setData(
            [
                'code' => $code,
                'message' => $message
            ]
        );
        return $json;
    }
}