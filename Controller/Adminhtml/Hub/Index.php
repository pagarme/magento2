<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $configWriter;
    protected $cacheManager;
    protected $requestObject;
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WriterInterface $configWriter,
        Manager $cacheManager,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->requestObject = $request;
        $this->storeManager = $storeManager;

        parent::__construct($context);
        Magento2CoreSetup::bootstrap();
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (isset($_GET['authorization_code'])) {
            $params = $this->requestObject->getParams();

            $hubIntegrationService = new HubIntegrationService();
            $hubIntegrationService->endHubIntegration(
                $params['&install_token'],
                $params['authorization_code'],
                $this->getCallbackUrl()
            );

            $this->updateStoreFields();
        }

        $url = $this->getUrl('adminhtml/system_config/edit/section/payment');
        header('Location: ' . explode('?', $url)[0]);
        exit;
    }

    private function getCallbackUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "rest/V1/pagarme/hub/command";
    }

    private function updateStoreFields()
    {

        $actualConfigurations = Magento2CoreSetup::getModuleConfiguration();

        $this->configWriter->save(
            "pagarme_pagarme/hub/install_id",
            $actualConfigurations->getHubInstallId()->getValue()
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/access_token",
            $actualConfigurations->getSecretKey()->getValue()
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key",
            $actualConfigurations->getSecretKey()->getValue()
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key",
            $actualConfigurations->getPublicKey()->getValue()
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/test_mode",
            0
        );

        $this->cacheManager->clean(['config']);
    }
}
