<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Store\Model\StoreManagerInterface;
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
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->requestObject->getParams();
        $websiteId = isset($params['website'])
            ? $params['website']
            : $this->storeManager->getDefaultStoreView()->getWebsiteId();

        $storeId = $this->storeManager->getWebsite($websiteId)
            ->getDefaultStore()->getId();
        $this->storeManager->setCurrentStore($storeId);

        Magento2CoreSetup::bootstrap();

        if (isset($params['authorization_code'])) {
            try {
                $hubIntegrationService = new HubIntegrationService();
                $hubIntegrationService->endHubIntegration(
                    $params['&install_token'],
                    $params['authorization_code'],
                    $this->getCallbackUrl($websiteId),
                    $this->getWebHookkUrl()
                );
                $this->updateStoreFields($websiteId);
            } catch (\Throwable $error) {
                throw new MagentoException(
                    __($error->getMessage()),
                    0,
                    400
                );
            }
        }

        $url = $this->getUrl('adminhtml/system_config/edit/section/payment');
        header('Location: ' . explode('?', $url)[0] . 'website/' . $websiteId);
        exit;
    }

    private function getCallbackUrl($websiteId)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "rest/V1/pagarme/hub/command?websiteId=" . $websiteId;
    }

    private function getWebHookkUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "rest/V1/pagarme/webhook";
    }

    private function allWebsitesIntegrated($currentWebsiteId)
    {
        $websites = $this->storeManager->getWebsites();
        $magento2CoreSetup = new Magento2CoreSetup();

        foreach ($websites as $websiteId => $website) {
            $storeId = $this->storeManager->getWebsite($websiteId)
                ->getDefaultStore()->getId();

            $this->storeManager->setCurrentStore($storeId);

            $magento2CoreSetup->loadModuleConfigurationFromPlatform();
            $currentConfiguration = Magento2CoreSetup::getModuleConfiguration();

            $isSameWebsite = $websiteId === intval($currentWebsiteId);

            if (!$isSameWebsite && !$currentConfiguration->isHubEnabled()) {
                return false;
            }
        }

        return true;
    }

    private function removeDefaultConfigKeys()
    {
        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key",
            null,
            'default',
            0
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key",
            null,
            'default',
            0
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key_test",
            null,
            'default',
            0
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key_test",
            null,
            'default',
            0
        );
    }

    private function updateStoreFields($websiteId)
    {
        $currentConfiguration = Magento2CoreSetup::getModuleConfiguration();

        $this->configWriter->save(
            "pagarme_pagarme/hub/install_id",
            $currentConfiguration->getHubInstallId()->getValue(),
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/environment",
            $currentConfiguration->getHubEnvironment()->getValue(),
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key",
            $currentConfiguration->getSecretKey()->getValue(),
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key",
            $currentConfiguration->getPublicKey()->getValue(),
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/test_mode",
            0,
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key_test",
            null,
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key_test",
            null,
            'websites',
            $websiteId
        );

        if ($this->allWebsitesIntegrated($websiteId)) {
            $this->removeDefaultConfigKeys();
        }

        $this->cacheManager->clean(['config']);
    }
}
