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
use Pagarme\Pagarme\Model\Validation\DashSettingsValidation;
use Pagarme\Pagarme\Service\AccountService;

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
     * @var DashSettingsValidation
     */
    protected $dashSettingsValidation;

    /**
     * @var HubCommand
     */
    protected $hubCommand;

    /**
     * @var CollectionFactory
     */
    protected $configCollectionFactory;

    public function __construct(
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        AccountService $accountService,
        DashSettingsValidation $dashSettingsValidation,
        HubCommand $hubCommand,
        CollectionFactory $configCollectionFactory
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->accountService = $accountService;
        $this->dashSettingsValidation = $dashSettingsValidation;
        $this->hubCommand = $hubCommand;
        $this->configCollectionFactory = $configCollectionFactory;
        Magento2CoreSetup::bootstrap();
        $this->config = Magento2CoreSetup::getModuleConfiguration();
    }

    public function validateDashSettings()
    {
        if (
            empty($this->config->getHubInstallId())
            || empty($this->getAccountId())
        ) {
            return;
        }

        try {
            $accountInfo = $this->accountService->getAccount($this->getAccountId());
            $errorsList = $this->dashSettingsValidation->validate($accountInfo);
            $this->configWriter->save(
                ConfigInterface::PATH_DASH_ERRORS,
                json_encode($errorsList),
                ScopeInterface::SCOPE_WEBSITES,
                $this->storeManager->getStore()
                    ->getWebsiteId()
            );
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid API key') {
                $this->hubCommand->uninstallCommand();
            }
        }
    }

    /**
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

    public function getAccountId()
    {
        return $this->config->getAccountId() ?? null;
    }

    public function getMerchantId()
    {
        return $this->config->getMerchantId() ?? null;
    }
}
