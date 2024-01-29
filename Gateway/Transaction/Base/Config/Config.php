<?php

/**
 * Class Config
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Config;

class Config extends AbstractConfig implements ConfigInterface
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfig(static::PATH_ENABLED);
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->getTestMode()) {
            return $this->getConfig(static::PATH_SECRET_KEY_TEST);
        }

        return $this->getConfig(static::PATH_SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        if ($this->getTestMode()) {
            return $this->getConfig(static::PATH_PUBLIC_KEY_TEST);
        }

        return $this->getConfig(static::PATH_PUBLIC_KEY);
    }
    /**
     * @return string
     */
    public function getAlwaysCreateOrder()
    {
        return $this->getConfig('pagarme_pagarme/global/create_order');
    }
    
    /**
     * @return bool
     */
    public function isHubEnabled()
    {
        $hubInstallKey = $this->getConfig(static::PATH_HUB_INSTALL_ID);

        return !empty($hubInstallKey);
    }

    /**
     * @return ?string|null
     */
    public function getHubEnvironment(): ?string
    {
        return $this->getConfig(static::PATH_HUB_ENVIRONMENT);
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return ( $this->getHubEnvironment() === static::HUB_SANDBOX_ENVIRONMENT ||
            strpos($this->getSecretKey() ?? '', 'sk_test') !== false ||
            strpos($this->getPublicKey() ?? '', 'pk_test') !== false
        );
    }

    /**
     * @return string
     */
    public function getTestMode()
    {
        return $this->getConfig(static::PATH_TEST_MODE);
    }

    /**
     * @return string
     */
    public function getCustomerStreetAttribute()
    {
        return $this->getConfig(static::PATH_CUSTOMER_STREET);
    }

    /**
     * @return string
     */
    public function getCustomerAddressNumber()
    {
        return $this->getConfig(static::PATH_CUSTOMER_NUMBER);
    }

    /**
     * @return string
     */
    public function getCustomerAddressComplement()
    {
        return $this->getConfig(static::PATH_CUSTOMER_COMPLEMENT);
    }

    /**
     * @return string
     */
    public function getCustomerAddressDistrict()
    {
        return $this->getConfig(static::PATH_CUSTOMER_DISTRICT);
    }

    /**
     * @return bool
     */
    public function isSendEmail()
    {
        $sendEmail = $this->getConfig(static::PATH_SEND_EMAIL);

        if ($sendEmail == '1') {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPagarmeCustomerConfigs()
    {
        return [
            'showVatNumber' => $this->getConfig(static::PATH_CUSTOMER_VAT_NUMBER) ?? '',
            'streetLinesNumber' => $this->getConfig(static::PATH_CUSTOMER_ADDRESS_LINES) ?? '',
        ];
    }

    /**
     * @return array
     */
    public function getPagarmeCustomerAddressConfigs()
    {
        return [
            'street' => $this->getConfig(static::PATH_CUSTOMER_ADDRESS_STREET) ?? '',
            'number' => $this->getConfig(static::PATH_CUSTOMER_ADDRESS_NUMBER) ?? '',
            'complement' => $this->getConfig(static::PATH_CUSTOMER_ADDRESS_COMPLEMENT) ?? '',
            'neighborhood' => $this->getConfig(static::PATH_CUSTOMER_ADDRESS_NEIGHBOURHOOD) ?? '',
        ];
    }
}
