<?php

namespace Pagarme\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Pagarme\Core\Payment\Repositories\CustomerRepository;

class Magento2PlatformCustomerDecorator implements PlatformCustomerInterface
{
    protected $platformCustomer;

    /** @var CustomerId */
    protected $pagarmeId;

    public function __construct($platformCustomer = null)
    {
        $this->platformCustomer = $platformCustomer;
    }

    public function getCode()
    {
        return $this->platformCustomer->getId();
    }

    /**
     * @return CustomerId|null
     */
    public function getPagarmeId()
    {
        if ($this->pagarmeId !== null) {
            return $this->pagarmeId;
        }

        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findByCode($this->platformCustomer->getId());

        if ($customer !== null) {
            $this->pagarmeId = $customer->getPagarmeId()->getValue();
            return $this->pagarmeId;
        }

        /** @var  $mpIdLegado deprecated */
        $mpIdLegado = $this->platformCustomer->getCustomAttribute('customer_id_pagarme');
        if (!empty($mpIdLegado->getValue())) {
            $this->pagarmeId = $mpIdLegado;
            return $this->pagarmeId;
        }

        return null;
    }

    public function getName()
    {
        $fullname = [
            $this->platformCustomer->getFirstname(),
            $this->platformCustomer->getMiddlename(),
            $this->platformCustomer->getLastname()
        ];

        return implode(" ", $fullname);
    }

    public function getEmail()
    {
        return $this->platformCustomer->getEmail();
    }

    public function getDocument()
    {
        if (!empty($this->platformCustomer->getTaxvat())) {
            return $this->platformCustomer->getTaxvat();
        }
        return null;
    }

    public function getType()
    {
        return CustomerType::individual();
    }

    public function getAddress()
    {
        /** @TODO */
    }

    public function getPhones()
    {
        /** @TODO */
    }

}
