<?php

namespace MundiPagg\MundiPagg\Plugin\Admin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use MundiPagg\MundiPagg\Helper\CustomerUpdatePagarmeHelper;

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
    protected $customerUpdateMundipaggHelper;

    /**
     * CustomerPlugin constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param CustomerUpdatePagarmeHelper $customerUpdateMundipaggHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        CustomerUpdatePagarmeHelper $customerUpdateMundipaggHelper
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->customerUpdateMundipaggHelper = $customerUpdateMundipaggHelper;
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

        $this->customerUpdateMundipaggHelper->updateEmailPagarme($customer);

        return $subject;

    }

}
