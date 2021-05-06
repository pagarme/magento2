<?php

namespace Pagarme\Pagarme\Model;

use \Magento\Store\Model\ScopeInterface;

/**
 * Class PagarmeConfigProvider
 *
 * @package Pagarme\Pagarme\Model
 */
class PagarmeConfigProvider
{
    /**
     * Contains if the module is active or not
     */
    const XML_PATH_ADVANCED_SETTINGS_ENABLED  = 'pagarme_pagarme/integration_type/integration';
    const XML_PATH_SOFTDESCRIPTION   = 'payment/pagarme_creditcard/soft_description';
    const XML_PATH_MAX_INSTALLMENT   = 'payment/pagarme_creditcard/installments_number';
    const XML_PATH_ACTIVE            = 'pagarme_pagarme/global/active';
    const XML_PATH_VOUCHER_ACTIVE    = 'payment/pagarme_voucher/active';
    const XML_PATH_DEBIT_ACTIVE      = 'payment/pagarme_debit/active';
    const XML_PATH_RECURRENCE_ACTIVE = 'pagarme_pagarme/recurrence/active';
    const PATH_CUSTOMER_STREET       = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER       = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT   = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT     = 'payment/pagarme_customer_address/district_attribute';

    /**
     * Contains scope config of Magento
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Contains the configurations
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Returns the soft_description configuration
     *
     * @return string
     */
    public function getSoftDescription()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOFTDESCRIPTION,
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

    public function getAdvancedSettingsIsEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADVANCED_SETTINGS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function validateSoftDescription()
    {
        $advancedSettingsIsEnabled = $this->getAdvancedSettingsIsEnabled();
        $softDescription = $this->getSoftDescription();
        $maxSizeForGateway = 22;
        $maxSizeForPSP = 13;

        if (
            $advancedSettingsIsEnabled
            && strlen($softDescription) > $maxSizeForGateway
        ) {
            $newResult = substr($softDescription, 0, $maxSizeForGateway - 1);
            $this->config->saveConfig(
                self::XML_PATH_SOFTDESCRIPTION,
                $newResult,
                'default',
                0
            );

            return false;
        }

        if (
            !$advancedSettingsIsEnabled
            && strlen($softDescription) > $maxSizeForPSP
        ) {
            $newResult = substr($softDescription, 0, $maxSizeForPSP - 1);
            $this->config->saveConfig(
                self::XML_PATH_SOFTDESCRIPTION,
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
        $advancedSettingsIsEnabled = $this->getAdvancedSettingsIsEnabled();
        $maxInstallment = $this->getMaxInstallment();
        $maxInstallmentForPSP = 12;

        if (
            !$advancedSettingsIsEnabled
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
}
