<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Charges;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

use Magento\Framework\App\Request\Http;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;


class ChargeAction extends \Magento\Backend\App\Action
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
     * Capture action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

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
