<?php

namespace Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway;

use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;

abstract class AbstractAddressDataProvider extends AbstractRequestDataProvider
{

    protected abstract function getConfig();

    /**
     * @return string
     */
    public function getCustomerAddressStreet($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerStreetAttribute());
        }

        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerStreetAttribute());
    }

    /**
     * @return string
     */
    public function getCustomerAddressNumber($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressNumber());
        }

        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerAddressNumber());
    }

    /**
     * @return string
     */
    public function getCustomerAddressComplement($shipping)
    {
        if ($shipping) {
            return !$this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressComplement()) ? 'street_3' : $this->getConfig()->getCustomerAddressComplement();
        }
        
        return !$this->getBillingAddressAttribute($this->getConfig()->getCustomerAddressComplement()) ? 'street_3' : $this->getConfig()->getCustomerAddressComplement();
    }

    /**
     * @return string
     */
    public function getCustomerAddressDistrict($shipping)
    {
        if ($shipping) {
            $streetLine = !$this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? 'street_4' : $this->getConfig()->getCustomerAddressDistrict();
            return $this->getShippingAddressAttribute($streetLine);
        }
        $streetLine = !$this->getBillingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? 'street_4' : $this->getConfig()->getCustomerAddressDistrict();
        return $this->getBillingAddressAttribute($streetLine);
    }

    
}
