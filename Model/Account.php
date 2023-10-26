<?php

namespace Pagarme\Pagarme\Model;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface;
use Pagarme\Pagarme\Model\Api\HubCommand;
use Pagarme\Pagarme\Service\AccountService;
use Psr\Log\LoggerInterface;

class Account
{
    /**
     * @var AccountService
     */
    protected $accountService;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HubCommand
     */
    protected $hubCommand;

    /**
     * @var CollectionFactory
     */
    protected $configCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param AccountService $accountService
     * @param HubCommand $hubCommand
     * @param CollectionFactory $configCollectionFactory
     * @param LoggerInterface $logger
     * @param Session $session
     */
    public function __construct(
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        AccountService $accountService,
        HubCommand $hubCommand,
        CollectionFactory $configCollectionFactory,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->accountService = $accountService;
        $this->hubCommand = $hubCommand;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @param mixed $website
     * @return void
     */
    public function validateDashSettings($website)
    {
        $this->session->setWebsiteId($website);
        $this->initializeConfig($website);
        if (
            empty($this->config->getHubInstallId())
            || empty($this->getAccountId())
        ) {
            return;
        }

        try {
            $account = $this->accountService->getAccountWithValidation($this->getAccountId(), $website);
            $this->configWriter->save(
                ConfigInterface::PATH_DASH_ERRORS,
                json_encode($account->getErrors()),
                ScopeInterface::SCOPE_WEBSITES,
                $website
            );
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid API key') {
                $this->hubCommand->uninstallCommand();
            }
            $this->logger->error(__('Failed to get account information: %1', $e->getMessage()));
        }
    }

    /**
     * @param $account
     * @return void
     * @throws NoSuchEntityException
     */
    public function saveAccountIdFromWebhook($account)
    {
        if ($this->getAccountId() || empty($account) || empty($account['id'])) {
            return;
        }

        $this->configWriter->save(
            ConfigInterface::PATH_ACCOUNT_ID,
            $account['id'],
            ScopeInterface::SCOPE_WEBSITES,
            $this->storeManager->getStore()
                ->getWebsiteId()
        );
    }

    /**
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getDashSettingsErrors()
    {
        $this->initializeConfig();
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => ConfigInterface::PATH_DASH_ERRORS]);
        $collection->addFieldToFilter('scope', ['eq' => ScopeInterface::SCOPE_WEBSITES]);
        $collection->addFieldToFilter('scope_id', ['eq' => $this->session->getWebsiteId()]);

        if ($collection->count() === 0) {
            return [];
        }

        $errorsList = $collection->getFirstItem()->getData()['value'];
        $returnData = json_decode($errorsList);
        if (empty($returnData)) {
            return [];
        }

        return $returnData;
    }

    /**
     * @return string|null
     */
    public function getAccountId()
    {
        $this->initializeConfig();
        return $this->config->getAccountId() ?? null;
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->config->getMerchantId() ?? null;
    }

    /**
     * @return bool
     */
    public function hasMerchantAndAccountIds()
    {
        return $this->getAccountId() && $this->getMerchantId();
    }

    /**
     * @return mixed
     */
    public function getDashUrl() {
        if (!$this->hasMerchantAndAccountIds()) {
            return null;
        }
        return sprintf(
            'https://dash.pagar.me/%s/%s/',
            $this->getMerchantId(),
            $this->getAccountId()
        );
    }

    private function initializeConfig($website = null)
    {
        if (empty($this->config)) {
            $websiteId = $website ?? $this->session->getWebsiteId();
            $storeId = $this->storeManager->getWebsite($websiteId)
                ->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            Magento2CoreSetup::bootstrap();
            $this->config = Magento2CoreSetup::getModuleConfiguration();
        }
    }
}
