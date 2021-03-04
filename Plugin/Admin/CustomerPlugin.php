<?php

namespace Pagarme\Pagarme\Plugin\Admin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Pagarme\Pagarme\Helper\CustomerUpdatePagarmeHelper;

class CustomerPlugin
{

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerUpdatePagarmeHelper
     */
    protected $customerUpdatePagarmeHelper;

    /**
     * CustomerPlugin constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param CustomerUpdatePagarmeHelper $customerUpdatePagarmeHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        CustomerUpdatePagarmeHelper $customerUpdatePagarmeHelper
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->customerUpdatePagarmeHelper = $customerUpdatePagarmeHelper;
    }

    /**
     * @param $subject
     * @return mixed
     */
    public function beforeExecute($subject)
    {

        $user = $subject->getRequest()->getPost()->get('items');
        $userData = array_shift($user);

        $customer = $this->customerFactory->create();
        $customer->setId($userData['entity_id']);
        $customer->setEmail($userData['email']);

        $this->customerUpdatePagarmeHelper->updateEmailPagarme($customer);

        return $subject;

    }

}
