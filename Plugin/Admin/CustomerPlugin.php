<?php

namespace MundiPagg\MundiPagg\Plugin\Admin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use MundiPagg\MundiPagg\Helper\CustomerUpdateMundipaggHelper;

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
     * @var CustomerUpdateMundipaggHelper
     */
    protected $customerUpdateMundipaggHelper;

    /**
     * CustomerPlugin constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param CustomerUpdateMundipaggHelper $customerUpdateMundipaggHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        CustomerUpdateMundipaggHelper $customerUpdateMundipaggHelper
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

        $this->customerUpdateMundipaggHelper->updateEmailMundipagg($customer);

        return $subject;

    }

}