<?php

namespace Pagarme\Pagarme\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
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
    const XML_PATH_IS_GATEWAY_INTEGRATION_TYPE  = 'pagarme_pagarme/global/is_gateway_integration_type';
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
    private $pagarmeConfig;

    /**
     * @param PagarmeConfigInterface $pagarmeConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        PagarmeConfigInterface $pagarmeConfig,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->pagarmeConfig = $pagarmeConfig;
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
            ScopeInterface::SCOPE_STORE
        );
    }

    public function validateSoftDescription()
    {
        $isGatewayIntegrationType = $this->isGatewayIntegrationType();
        $softDescription = $this->getSoftDescription();
        $maxSizeForGateway = 22;
        $maxSizeForPSP = 13;

        if (
            $isGatewayIntegrationType
            && mb_strlen($softDescription) > $maxSizeForGateway
        ) {
            $newResult = substr($softDescription, 0, $maxSizeForGateway);
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
            $newResult = substr($softDescription, 0, $maxSizeForPSP);
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

    public function disableVoucher()
    {
        $this->config->saveConfig(
            self::XML_PATH_VOUCHER_ACTIVE,
            0,
            'default',
            0
        );
    }

    public function disableDebit()
    {
        $this->config->saveConfig(
            self::XML_PATH_DEBIT_ACTIVE,
            0,
            'default',
            0
        );
    }

    public function disableRecurrence()
    {
        $this->config->saveConfig(
            self::XML_PATH_RECURRENCE_ACTIVE,
            0,
            'default',
            0
        );
    }

    public function disableAntifraud()
    {
        $this->config->saveConfig(
            self::XML_PATH_ANTIFRAUD_ACTIVE,
            0,
            'default',
            0
        );
    }

    public function disableSavedCard()
    {
        $this->config->saveConfig(
            self::XML_PATH_IS_ENABLE_SAVED_CARDS,
            0,
            'default',
            0
        );
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return ['pagarme_is_sandbox_mode' => $this->pagarmeConfig->isSandboxMode()] ;
    }
}
