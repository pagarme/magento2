<?php

namespace Pagarme\Pagarme\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Middle\Model\Account;
use Pagarme\Core\Middle\Model\Account\StoreSettings;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Model\CoreAuth;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class AccountService
{
    /**
     * @var CoreAuth
     */
    protected $coreAuth;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PagarmeConfigProvider
     */
    protected $configProvider;

    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config,
        PagarmeConfigProvider $configProvider
    )
    {
        $this->coreAuth = new CoreAuth();
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->configProvider = $configProvider;
    }

    /**
     * @param mixed $accountId
     * @param mixed $website
     * @return Account
     * @throws NoSuchEntityException
     */
    public function getAccountWithValidation($accountId, $website = 1)
    {
        $storeSettings = new StoreSettings();
        $storeSettings->setSandbox($this->config->isSandboxMode());
        $stores = $this->storeManager->getWebsite($website)
            ->getStores();
        $storeSettings->setStoreUrls(
            array_map([$this, 'mapBaseUrlFromStore'], $stores)
        );
        $storeSettings->setEnabledPaymentMethods($this->configProvider->availablePaymentMethods($website));

        $accountResponse = $this->getAccountOnPagarme($accountId);

        $account = Account::createFromSdk($accountResponse);
        return $account->validate($storeSettings);
    }

    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }

    private function mapBaseUrlFromStore($store)
    {
        return $store->getBaseUrl();
    }
}
