<?php

namespace Pagarme\Pagarme\Model\Validation;

use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class DashSettingsValidation
{
    const ACCOUNT_DISABLED = 'accountDisabled';

    const DOMAIN_EMPTY = 'domainEmpty';

    const DOMAIN_INCORRECT = 'domainIncorrect';

    const WEBHOOK_INCORRECT = 'webhookIncorrect';

    const MULTIPAYMENTS_DISABLED = 'multiPaymentsDisabled';

    const MULTIBUYERS_DISABLED = 'multiBuyersDisabled';

    const PIX_DISABLED = 'pixDisabled';

    const CREDIT_CARD_DISABLED = 'creditCardDisabled';

    const BILLET_DISABLED = 'billetDisabled';

    const VOUCHER_DISABLED = 'voucherDisabled';

    const DEBIT_DISABLED = 'debitDisabled';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @var array
     */
    private $hubAccountErrors = [];

    /**
     * @param $accountInfo
     * @return array
     */
    public function validate($accountInfo)
    {
        $this->isAccountEnabled($accountInfo);
        $this->isDomainCorrect($accountInfo);
        $this->isWebHookCorrect($accountInfo);
        $this->isMultiBuyersEnabled($accountInfo);
        $this->isMultiPaymentsEnabled($accountInfo);
        $this->isPixEnabled($accountInfo);
        $this->isCreditCardEnabled($accountInfo);
        $this->isBilletEnabled($accountInfo);
        $this->isVoucherEnabled($accountInfo);
        $this->isDebitEnabled($accountInfo);

        return $this->hubAccountErrors;
    }

    private function isMultiPaymentsEnabled($accountInfo)
    {
        $orderSettings = $accountInfo->orderSettings;
        if (!$orderSettings['multi_payments_enabled']) {
            $this->hubAccountErrors[] = self::MULTIPAYMENTS_DISABLED;
        }
    }

    private function isMultiBuyersEnabled($accountInfo)
    {
        $orderSettings = $accountInfo->orderSettings;
        if (!$orderSettings['multi_buyers_enabled']) {
            $this->hubAccountErrors[] = self::MULTIBUYERS_DISABLED;
        }
    }

    private function isAccountEnabled($accountInfo)
    {
        if ($accountInfo->status !== 'active') {
            $this->hubAccountErrors[] = self::ACCOUNT_DISABLED;
        }
    }

    private function isDomainCorrect($accountInfo)
    {
        if ($this->config->isSandboxMode()) {
            return;
        }
        $domains = $accountInfo->domains;
        if (empty($domains)) {
            $this->hubAccountErrors[] = self::DOMAIN_EMPTY;
            return;
        }

        $siteUrl = $this->storeManager->getStore()
            ->getBaseUrl();
        foreach ($domains as $domain) {
            if (strpos($siteUrl, $domain) !== false) {
                return;
            }
        }

        $this->hubAccountErrors[] = self::DOMAIN_INCORRECT;
    }

    private function isWebHookCorrect($accountInfo)
    {
        $siteUrl = $this->storeManager->getStore()
            ->getBaseUrl();
        foreach ($accountInfo->webhookSettings as $webhook) {
            if (strpos($webhook->url, $siteUrl) !== false) {
                return;
            }
        }
        $this->hubAccountErrors[] = self::WEBHOOK_INCORRECT;
    }

    private function isPixEnabled($accountInfo)
    {
        $storePixEnabled = $this->config->isPixEnabled();
        $dashPixDisabled = !$accountInfo->pixSettings['enabled'];
        if ($dashPixDisabled && $storePixEnabled) {
            $this->hubAccountErrors[] = self::PIX_DISABLED;
        }
    }

    private function isCreditCardEnabled($accountInfo)
    {
        $storeCreditCardEnabled = $this->config->isAnyCreditCardMethodEnabled();
        $dashCreditCardDisabled = !$accountInfo->creditCardSettings['enabled'];
        if ($dashCreditCardDisabled && $storeCreditCardEnabled) {
            $this->hubAccountErrors[] = self::CREDIT_CARD_DISABLED;
        }
    }

    private function isBilletEnabled($accountInfo)
    {
        $storeBilletEnabled = $this->config->isAnyBilletMethodEnabled();
        $dashBilletDisabled = !$accountInfo->boletoSettings['enabled'];
        if ($dashBilletDisabled && $storeBilletEnabled) {
            $this->hubAccountErrors[] = self::BILLET_DISABLED;
        }
    }

    private function isVoucherEnabled($accountInfo)
    {
        $storeVoucherEnabled = $this->config->isVoucherEnabled();
        $dashVoucherDisabled = !$accountInfo->voucherSettings['enabled'];
        if ($dashVoucherDisabled && $storeVoucherEnabled) {
            $this->hubAccountErrors[] = self::VOUCHER_DISABLED;
        }
    }

    private function isDebitEnabled($accountInfo)
    {
        $storeDebitEnabled = $this->config->isDebitEnabled();
        $dashDebitDisabled = !$accountInfo->debitSettings['enabled'];
        if ($dashDebitDisabled && $storeDebitEnabled) {
            $this->hubAccountErrors[] = self::DEBIT_DISABLED;
        }
    }
}
