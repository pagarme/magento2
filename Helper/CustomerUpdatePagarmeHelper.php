<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 02/05/18
 * Time: 15:25
 */

namespace Pagarme\Pagarme\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Pagarme\Core\Payment\Services\CustomerService;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class CustomerUpdatePagarmeHelper
{

    protected $updateCustomerRequest;

    protected $config;

    protected $customerRepositoryInterface;

    /**
     * AdminCustomerSaveAfter constructor.
     */
    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerService $customerService
    ) {
        $this->config = $config;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerService = $customerService;
    }


    /**
     * @param $customer
     * @return void
     */
    public function updateEmailPagarme($customer)
    {
        $oldCustomer = $this->customerRepositoryInterface->getById($customer->getId());
        if($oldCustomer->getCustomAttribute('customer_id_pagarme') && ($oldCustomer->getEmail() != $customer->getEmail())){
            $this->customerService->updateCustomerAtPagarme($customer);
        }
    }

}