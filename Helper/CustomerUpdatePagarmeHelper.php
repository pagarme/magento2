<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 02/05/18
 * Time: 15:25
 */

namespace Pagarme\Pagarme\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use MundiAPILib\Controllers;
use MundiAPILib\Models\UpdateCustomerRequest;
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
        UpdateCustomerRequest $updateCustomerRequest,
        Config $config,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->updateCustomerRequest = $updateCustomerRequest;
        $this->config = $config;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }


    /**
     * @param $customer
     * @return void
     */
    public function updateEmailPagarme($customer)
    {

        $oldCustomer = $this->customerRepositoryInterface->getById($customer->getId());

        if($oldCustomer->getCustomAttribute('customer_id_pagarme') && ($oldCustomer->getEmail() != $customer->getEmail())){

            $customerIdPagarme = $oldCustomer->getCustomAttribute('customer_id_pagarme')->getValue();

            $this->updateCustomerRequest->email = $customer->getEmail();
            $this->updateCustomerRequest->name = $oldCustomer->getFirstName() . ' ' . $oldCustomer->getLastName();
            $this->updateCustomerRequest->document = preg_replace('/[\/.-]/', '', $oldCustomer->getTaxvat());
            $this->updateCustomerRequest->type = 'individual';

            $this->getApi()->getCustomers()->updateCustomer($customerIdPagarme, $this->updateCustomerRequest);

        }

    }

    /**
     * Singleton access to Customers controller
     * @return Controllers\CustomersController The *Singleton* instance
     */
    public function getCustomers()
    {
        return Controllers\CustomersController::getInstance();
    }

    /**
     * @return \MundiAPILib\MundiAPIClient
     */
    public function getApi()
    {
        return new \MundiAPILib\MundiAPIClient($this->config->getSecretKey(), '');
    }

}
