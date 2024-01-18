<?php
/**
 * Class AbstractConfig
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Config;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfig implements CustomerConfigInterface
{
    protected $storeConfig;

    /**
     * @param ScopeConfigInterface $storeConfig
     */
    public function __construct(
        ScopeConfigInterface $storeConfig
    )
    {
        $this->setStoreConfig($storeConfig);
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     */
    protected function getConfig($path, $store = null)
    {
        if (! $store){
            $store = ScopeInterface::SCOPE_STORE;
        }

        return $this->getStoreConfig()->getValue($path, $store);
    }

    /**
     * @return ScopeConfigInterface
     */
    protected function getStoreConfig()
    {
        return $this->storeConfig;
    }

    /**
     * @param ScopeConfigInterface $storeConfig
     * @return self
     */
    protected function setStoreConfig(ScopeConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
        return $this;
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
}
