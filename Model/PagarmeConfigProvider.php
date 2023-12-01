<?php

namespace Pagarme\Pagarme\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface as PagarmeConfigInterface;

/**
 * Class PagarmeConfigProvider
 *
 * @package Pagarme\Pagarme\Model
 */
class PagarmeConfigProvider implements ConfigProviderInterface
{
    /**
     * Contains if the module is active or not
     */
    const XML_PATH_IS_GATEWAY_INTEGRATION_TYPE      = 'pagarme_pagarme/global/is_gateway_integration_type';
    const XML_PATH_RECURRENCE_ADD_SHIPPING_IN_ITEMS = 'pagarme_pagarme/recurrence/add_shipping_in_items';
    const XML_PATH_RECURRENCE_ADD_TAX_IN_ITEMS      = 'pagarme_pagarme/recurrence/add_tax_in_items';
    const XML_PATH_IS_ENABLE_SAVED_CARDS = 'payment/pagarme_creditcard/enabled_saved_cards';
    const XML_PATH_SOFT_DESCRIPTION      = 'payment/pagarme_creditcard/soft_description';
    const XML_PATH_MAX_INSTALLMENT       = 'payment/pagarme_creditcard/installments_number';
    const XML_PATH_ACTIVE                = 'pagarme_pagarme/global/active';
    const XML_PATH_VOUCHER_ACTIVE        = 'payment/pagarme_voucher/active';
    const XML_PATH_DEBIT_ACTIVE          = 'payment/pagarme_debit/active';
    const XML_PATH_RECURRENCE_ACTIVE     = 'pagarme_pagarme/recurrence/active';
    const XML_PATH_ANTIFRAUD_ACTIVE      = 'payment/pagarme_creditcard/antifraud_active';
    const PATH_CUSTOMER_STREET           = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER           = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT       = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT         = 'payment/pagarme_customer_address/district_attribute';
    const PATH_ACCOUNT_ID = 'pagarme_pagarme/hub/account_id';

    const PATH_PIX_ENABLED = 'payment/pagarme_pix/active';

    const PATH_CREDIT_CARD_ENABLED = 'payment/pagarme_creditcard/active';

    const PATH_BILLET_AND_CREDIT_CARD_ENABLED = 'payment/pagarme_multipleactionscreditcardbillet/active';

    const PATH_TWO_CREDIT_CARD_ENABLED = 'payment/pagarme_multipleactionstwocreditcard/active';

    const PATH_BILLET_ENABLED = 'payment/pagarme_billet/active';

    const PATH_VOUCHER_ENABLED = 'payment/pagarme_voucher/active';

    const PATH_DEBIT_ENABLED = 'payment/pagarme_debit/active';

    const PATH_IS_PAYMENT_GATEWAY_TYPE = 'pagarme_pagarme/%s/is_payment_gateway';

    const PATH_IS_PAYMENT_PSP_TYPE = 'pagarme_pagarme/%s/is_payment_psp';

    const BILLET_PAYMENT_CONFIG = 'pagarme_billet';

    const CREDIT_CARD_PAYMENT_CONFIG = 'pagarme_creditcard';

    const DEBIT_PAYMENT_CONFIG = 'pagarme_debit';

    const PIX_PAYMENT_CONFIG = 'pagarme_pix';

    const VOUCHER_PAYMENT_CONFIG = 'pagarme_voucher';

    const PATH_DASH_ERRORS = 'pagarme_pagarme/hub/account_errors';

    /**
     * Contains scope config of Magento
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Contains the configurations
     *
     * @var ConfigInterface
     */
    protected $config;

    /** @var PagarmeConfigInterface */
    protected $pagarmeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param PagarmeConfigInterface $pagarmeConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        PagarmeConfigInterface $pagarmeConfig,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->pagarmeConfig = $pagarmeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns the soft_description configuration
     *
     * @return string
     */
    public function getSoftDescription()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOFT_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMaxInstallment()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_INSTALLMENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isGatewayIntegrationType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_GATEWAY_INTEGRATION_TYPE,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function canAddShippingInItemsOnRecurrence()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RECURRENCE_ADD_SHIPPING_IN_ITEMS,
            ScopeInterface::SCOPE_STORE
        ) ?? false;
    }
    public function canAddTaxInItemsOnRecurrence()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RECURRENCE_ADD_TAX_IN_ITEMS,
            ScopeInterface::SCOPE_STORE
        ) ?? false;
    }

    public function validateSoftDescription()
    {
        $isGatewayIntegrationType = $this->isGatewayIntegrationType();
        $softDescription = $this->getSoftDescription() ?? "";
        $maxSizeForGateway = 22;
        $maxSizeForPSP = 13;

        if (
            $isGatewayIntegrationType
            && mb_strlen($softDescription) > $maxSizeForGateway
        ) {
            $newResult = mb_substr($softDescription, 0, $maxSizeForGateway);
            $this->config->saveConfig(
                self::XML_PATH_SOFT_DESCRIPTION,
                $newResult,
                'default',
                0
            );

            return false;
        }

        if (
            !$isGatewayIntegrationType
            && mb_strlen($softDescription) > $maxSizeForPSP
        ) {
            $newResult = mb_substr($softDescription, 0, $maxSizeForPSP);
            $this->config->saveConfig(
                self::XML_PATH_SOFT_DESCRIPTION,
                $newResult,
                'default',
                0
            );

            return false;
        }

        return true;
    }

    public function validateMaxInstallment()
    {
        $isGatewayIntegrationType = $this->isGatewayIntegrationType();
        $maxInstallment = $this->getMaxInstallment();
        $maxInstallmentForPSP = 12;

        if (
            !$isGatewayIntegrationType
            && $maxInstallment > $maxInstallmentForPSP
        ) {
            $this->config->saveConfig(
                self::XML_PATH_MAX_INSTALLMENT,
                $maxInstallmentForPSP,
                'default',
                0
            );

            return false;
        }

        return true;
    }

    /**
     * Returns the soft_description configuration
     *
     * @return string
     */
    public function getModuleStatus()
    {
        return
            $this->scopeConfig->getValue(
                self::XML_PATH_ACTIVE,
                ScopeInterface::SCOPE_STORE
            );
    }

    public function getCustomerAddressConfiguration()
    {
        $street = $this->scopeConfig->getValue(
            self::PATH_CUSTOMER_STREET,
            ScopeInterface::SCOPE_STORE
        );

        $number = $this->scopeConfig->getValue(
            self::PATH_CUSTOMER_NUMBER,
            ScopeInterface::SCOPE_STORE
        );

        $district = $this->scopeConfig->getValue(
            self::PATH_CUSTOMER_DISTRICT,
            ScopeInterface::SCOPE_STORE
        );

        return [
            'street' => $street,
            'number' => $number,
            'district' => $district
        ];
    }

    /**
     * @return mixed
     */
    public function isRecurrenceEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RECURRENCE_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isModuleOrRecurrenceDisabled()
    {
        return !$this->getModuleStatus()
            || !$this->isRecurrenceEnabled();
    }

    /**
     * @return string
     */
    public function getAccountId($website = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_ACCOUNT_ID,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isPixEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_PIX_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isCreditCardEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_CREDIT_CARD_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isBilletAndCreditCardEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_BILLET_AND_CREDIT_CARD_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isTwoCreditCardEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_TWO_CREDIT_CARD_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isAnyCreditCardMethodEnabled($website = null)
    {
        return $this->isCreditCardEnabled($website)
            || $this->isBilletAndCreditCardEnabled($website)
            || $this->isTwoCreditCardEnabled($website);
    }

    /**
     * @return bool
     */
    public function isBilletEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_BILLET_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isAnyBilletMethodEnabled($website = null)
    {
        return $this->isBilletEnabled($website)
            || $this->isBilletAndCreditCardEnabled($website);
    }

    /**
     * @return bool
     */
    public function isVoucherEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_VOUCHER_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $this->getWebsiteId($website)
        );
    }

    /**
     * @return bool
     */
    public function isDebitEnabled($website = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::PATH_DEBIT_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $website
        );
    }

    /**
     * @param mixed $website
     * @return array
     */
    public function availablePaymentMethods($website = null)
    {
        return [
            PaymentEnum::PIX => $this->isPixEnabled($website),
            PaymentEnum::DEBIT_CARD => $this->isDebitEnabled($website),
            PaymentEnum::BILLET => $this->isAnyBilletMethodEnabled($website),
            PaymentEnum::CREDIT_CARD => $this->isAnyCreditCardMethodEnabled($website),
            PaymentEnum::VOUCHER => $this->isVoucherEnabled($website)
        ];
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'pagarme_is_sandbox_mode' => $this->pagarmeConfig->isSandboxMode(),
            'pagarme_is_hub_enabled' => $this->pagarmeConfig->isHubEnabled(),
            'pagarme_customer_configs' => $this->pagarmeConfig->getPagarmeCustomerConfigs(),
            'pagarme_customer_address_configs' => $this->pagarmeConfig->getPagarmeCustomerAddressConfigs()
        ] ;
    }

    /**
     * @param mixed $websiteId
     * @return int|mixed
     * @throws NoSuchEntityException
     */
    private function getWebsiteId($websiteId = null)
    {
        return $websiteId ?? $this->storeManager->getStore()->getWebsiteId();
    }
}
