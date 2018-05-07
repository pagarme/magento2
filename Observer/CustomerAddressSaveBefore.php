<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\InputException;

class CustomerAddressSaveBefore implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $customerAddress = $observer->getCustomerAddress();

        $this->addressValidation($customerAddress);

        return $this;
    }

    /**
     * @param $customerAddress
     * @throws InputException
     */
    public function addressValidation($customerAddress)
    {
        if(empty($customerAddress->getStreetLine(1))){
            throw new InputException(__("Please check your address. First field of Street Address (Street) is required."));
        }

        if(empty($customerAddress->getStreetLine(2))){
            throw new InputException(__("Please check your address. Second field of Street Address (Number) is required."));
        }

        if(empty($customerAddress->getStreetLine(4))){
            throw new InputException(__("Please check your address. Fourth field of Street Address (Neighborhood) is required."));
        }
    }

}
