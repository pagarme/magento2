<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Service\AccountService;

class Account extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $accountService;

    private $config;

    private $accountInfo;

    private $notices;

    private $hubAccountErrors;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WriterInterface $configWriter,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        AccountService $accountService
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->configWriter = $configWriter;
        $this->requestObject = $request;
        $this->storeManager = $storeManager;
        $this->accountService = $accountService;
        Magento2CoreSetup::bootstrap();
        $this->config = Magento2CoreSetup::getModuleConfiguration();
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //return $this->handleResult(200, $message);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Get Agcount"));

        var_dump($this->getAccountInfoOnPagarme());
        exit();
        // return $resultPage;
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

    public function getAccountInfoOnPagarme()
    {
        if (
            empty($this->config->getHubInstallId())
            || empty($this->getAccountId())
        ) {
            return false;
        }

        try {
            $this->accountInfo = $this->accountService->getAccount($this->getAccountId());
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Invalid API key') {
                $this->removeHubIntegration();
            }
            return false;
        }
    }

    public function getAccountId()
    {
        return $this->config->getAccountId() ?? null;
    }

    public function getMerchantId()
    {
        return $this->config->getMerchantId() ?? null;
    }

    protected function _isAllowed()
    {
        return true;
    }

}
