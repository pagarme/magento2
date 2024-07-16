<?php

namespace Pagarme\Pagarme\Model;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Middle\Model\Account as AccountMiddle;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
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
     * @var PagarmeConfigProvider
     */
    protected $pagarmeConfigProvider;

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
        Session $session,
        PagarmeConfigProvider $pagarmeConfigProvider
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->accountService = $accountService;
        $this->hubCommand = $hubCommand;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->logger = $logger;
        $this->session = $session;
        $this->pagarmeConfigProvider = $pagarmeConfigProvider;
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
                PagarmeConfigProvider::PATH_DASH_ERRORS,
                json_encode($account->getErrors()),
                ScopeInterface::SCOPE_WEBSITES,
                $website
            );
            $this->savePaymentTypes($account, $website);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid API key') {
                $this->hubCommand->uninstallCommand();
            }
            $this->logger->error(__('Failed to get account information: %1', $e->getMessage()));
        }
    }

    /**
     * @param mixed $account
     * @return void
     * @throws NoSuchEntityException
     */
    public function saveAccountIdFromWebhook($account)
    {
        if ($this->getAccountId() || empty($account) || empty($account['id'])) {
            return;
        }

        $this->configWriter->save(
            PagarmeConfigProvider::PATH_ACCOUNT_ID,
            $account['id'],
            ScopeInterface::SCOPE_WEBSITES,
            $this->storeManager->getStore()
                ->getWebsiteId()
        );
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDashSettingsErrors()
    {
        $this->initializeConfig();
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => PagarmeConfigProvider::PATH_DASH_ERRORS]);
        $collection->addFieldToFilter('scope', ['eq' => ScopeInterface::SCOPE_WEBSITES]);
        $collection->addFieldToFilter('scope_id', ['eq' => $this->session->getWebsiteId()]);

        if ($collection->count() === 0) {
            return [];
        }

        $errorsList = $collection->getFirstItem()->getData()['value'] ?? '';
        $returnData = json_decode($errorsList);
        if (empty($returnData)) {
            return [];
        }

        return $returnData;
    }

    /**
     * @param string $paymentName
     * @param bool $gateway
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentType(string $paymentName, bool $gateway = true)
    {
        $this->initializeConfig();
        $paymentType = $gateway
            ? PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE
            : PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE;
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => sprintf($paymentType, $paymentName)]);
        $collection->addFieldToFilter('scope', ['eq' => ScopeInterface::SCOPE_WEBSITES]);
        $collection->addFieldToFilter('scope_id', ['eq' => $this->session->getWebsiteId()]);

        if ($collection->count() === 0) {
            return false;
        }

        return (bool)$collection->getFirstItem()->getData()['value'];
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
    public function getDashUrl()
    {
        if (!$this->hasMerchantAndAccountIds()) {
            return null;
        }
        return sprintf(
            'https://dash.pagar.me/%s/%s/',
            $this->getMerchantId(),
            $this->getAccountId()
        );
    }

    /**
     * @param string $paymentName
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isGateway(string $paymentName)
    {
        $this->initializeConfig();

        return (empty($this->getAccountId()) && $this->pagarmeConfigProvider->isGatewayIntegrationType())
            || $this->getPaymentType($paymentName);
    }

    /**
     * @param string $paymentName
     * @return bool
     */
    public function isPSP(string $paymentName)
    {
        return !empty($this->getAccountId()) && $this->getPaymentType($paymentName, false);
    }

    /**
     * @return void
     */
    public function clearWebsiteId()
    {
        $this->session->setWebsiteId(null);
    }

    /**
     * @param mixed $website
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function initializeConfig($website = null)
    {
        if (empty($this->config)) {
            $websiteId = $website;
            if (!$websiteId && $this->session->getWebsiteId()) {
                $websiteId = $this->session->getWebsiteId();
            }

            if (!$websiteId && $websiteId !== 0) {
                $websiteId = $this->storeManager->getStore()
                    ->getWebsiteId();
            }

            $storeId = $this->storeManager->getWebsite($websiteId)
                ->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            Magento2CoreSetup::bootstrap();
            $this->config = Magento2CoreSetup::getModuleConfiguration();
        }
    }

    /**
     * @param AccountMiddle $account
     * @param mixed $website
     * @return void
     */
    private function savePaymentTypes(AccountMiddle $account, $website)
    {
        $this->saveConfig(
            sprintf(
                PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE,
                PagarmeConfigProvider::CREDIT_CARD_PAYMENT_CONFIG
            ),
            (int)$account->getCreditCardSettings()->isGateway(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE, PagarmeConfigProvider::PIX_PAYMENT_CONFIG),
            (int)$account->getPixSettings()->isGateway(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE, PagarmeConfigProvider::VOUCHER_PAYMENT_CONFIG),
            (int)$account->getVoucherSettings()->isGateway(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE, PagarmeConfigProvider::BILLET_PAYMENT_CONFIG),
            (int)$account->getBilletSettings()->isGateway(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_GATEWAY_TYPE, PagarmeConfigProvider::DEBIT_PAYMENT_CONFIG),
            (int)$account->getDebitCardSettings()->isGateway(),
            $website
        );

        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE, PagarmeConfigProvider::CREDIT_CARD_PAYMENT_CONFIG),
            (int)$account->getCreditCardSettings()->isPSP(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE, PagarmeConfigProvider::PIX_PAYMENT_CONFIG),
            (int)$account->getPixSettings()->isPSP(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE, PagarmeConfigProvider::VOUCHER_PAYMENT_CONFIG),
            (int)$account->getVoucherSettings()->isPSP(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE, PagarmeConfigProvider::BILLET_PAYMENT_CONFIG),
            (int)$account->getBilletSettings()->isPSP(),
            $website
        );
        $this->saveConfig(
            sprintf(PagarmeConfigProvider::PATH_IS_PAYMENT_PSP_TYPE, PagarmeConfigProvider::DEBIT_PAYMENT_CONFIG),
            (int)$account->getDebitCardSettings()->isPSP(),
            $website
        );
    }

    /**
     * @param mixed $path
     * @param mixed $value
     * @param mixed $website
     * @return void
     */
    private function saveConfig($path, $value, $website)
    {
        $this->configWriter->save(
            $path,
            $value,
            ScopeInterface::SCOPE_WEBSITES,
            $website
        );
    }
}
