<?php

namespace Pagarme\Pagarme\Model;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
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
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param AccountService $accountService
     * @param HubCommand $hubCommand
     * @param CollectionFactory $configCollectionFactory
     * @param LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        AccountService $accountService,
        HubCommand $hubCommand,
        CollectionFactory $configCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->accountService = $accountService;
        $this->hubCommand = $hubCommand;
        $this->configCollectionFactory = $configCollectionFactory;
        Magento2CoreSetup::bootstrap();
        $this->config = Magento2CoreSetup::getModuleConfiguration();
        $this->logger = $logger;
    }

    /**
     * @param mixed $website
     * @return void
     */
    public function validateDashSettings($website)
    {
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
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => ConfigInterface::PATH_DASH_ERRORS]);
        $collection->addFieldToFilter('scope', ['eq' => ScopeInterface::SCOPE_WEBSITES]);
        $collection->addFieldToFilter('scope_id', ['eq' => $this->storeManager->getStore()->getWebsiteId()]);

        if ($collection->count() === 0) {
            return [];
        }

        $errorsList = $collection->getFirstItem()->getData()['value'];
        if (empty($errorsList)) {
            return [];
        }

        return json_decode($errorsList);
    }

    /**
     * @return string|null
     */
    public function getAccountId()
    {
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
        return $this->config->getAccountId() && $this->config->getMerchantId();
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
}
