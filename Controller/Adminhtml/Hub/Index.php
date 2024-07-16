<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Index extends Action
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
     * @return ResultInterface
     * @throws MagentoException|LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->requestObject->getParams();
        $scopeUrl = $this->getScopeUrl();
        $websiteId = $params['website'] ?? 0;
        $this->storeManager->setCurrentStore($websiteId);

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
        $header = 'Location: ' . explode('?', $url ?? '')[0];
        if (!empty($websiteId) && !empty($scopeUrl)) {
            $header .= $scopeUrl . '/' . $websiteId;
        }
        header($header);
        exit;
    }

    /**
     * @return string
     */
    public function getScopeName(): string
    {
        $request = $this->requestObject;

        if ($request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            return ScopeInterface::SCOPE_WEBSITES;
        }

        if ($request->getParam(ScopeInterface::SCOPE_STORE)) {
            return ScopeInterface::SCOPE_STORES;
        }

        return ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @return string|null
     */
    private function getScopeUrl()
    {
        $request = $this->requestObject;

        if ($request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            return ScopeInterface::SCOPE_WEBSITE;
        }

        if ($request->getParam(ScopeInterface::SCOPE_STORE)) {
            return ScopeInterface::SCOPE_STORE;
        }

        return null;
    }

    /**
     * @param $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCallbackUrl($websiteId)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $callbackUrl = $baseUrl . "rest/V1/pagarme/hub/command";

        if (!empty($websiteId)) {
            $callbackUrl .= "?websiteId=" . $websiteId;
        }

        return $callbackUrl;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getWebHookkUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "rest/V1/pagarme/webhook";
    }

    private function updateStoreFields($websiteId)
    {
        $currentConfiguration = Magento2CoreSetup::getModuleConfiguration();
        $scope = $this->getScopeName();

        $this->configWriter->save(
            "pagarme_pagarme/hub/install_id",
            $currentConfiguration->getHubInstallId()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/environment",
            $currentConfiguration->getHubEnvironment()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key",
            $currentConfiguration->getSecretKey()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key",
            $currentConfiguration->getPublicKey()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/account_id",
            $currentConfiguration->getAccountId()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/merchant_id",
            $currentConfiguration->getMerchantId()->getValue(),
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/test_mode",
            0,
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key_test",
            null,
            $scope,
            $websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key_test",
            null,
            $scope,
            $websiteId
        );

        $this->cacheManager->clean(['config']);
    }
}
