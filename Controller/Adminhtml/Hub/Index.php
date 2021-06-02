<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->requestObject = $request;

        $objectManager = ObjectManager::getInstance();

        $this->storeManager = $objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );

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
