<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MundiPagg\MundiPagg\Helper\CustomerUpdateMundipaggHelper;

class AdminCustomerBeforeSave implements ObserverInterface
{
    protected $customerUpdateMundipaggHelper;

    /**
     * AdminCustomerSaveAfter constructor.
     */
    public function __construct(
        CustomerUpdateMundipaggHelper $customerUpdateMundipaggHelper
    ) {
        $this->customerUpdateMundipaggHelper = $customerUpdateMundipaggHelper;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();

        $this->customerUpdateMundipaggHelper->updateEmailMundipagg($event->getCustomer());

    }

}
