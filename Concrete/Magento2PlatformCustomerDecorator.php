<?php

namespace MundiPagg\MundiPagg\Concrete;

use Mundipagg\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;

class Magento2PlatformCustomerDecorator implements PlatformCustomerInterface
{
    protected $platformCustomer;

    /** @var CustomerId */
    protected $mundipaggId;

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
    public function getMundipaggId()
    {
        if ($this->mundipaggId !== null) {
            return $this->mundipaggId;
        }

        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findByCode($this->platformCustomer->getId());

        if ($customer !== null) {
            $this->mundipaggId = $customer->getMundipaggId()->getValue();
            return $this->mundipaggId;
        }

        $mpIdLegado = $this->platformCustomer->getCustomAttribute('customer_id_mundipagg');
        if (!empty($mpIdLegado->getValue())) {
            $this->mundipaggId = $mpIdLegado;
            return $this->mundipaggId;
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
        return $this->platformCustomer->getTaxvat();
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
