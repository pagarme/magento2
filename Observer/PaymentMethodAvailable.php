<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;


class PaymentMethodAvailable implements ObserverInterface
{

    protected $mundiPaggConfigProvider;

    public function __construct(
        MundiPaggConfigProvider $mundiPaggConfigProvider
    ){
        $this->mundiPaggConfigProvider = $mundiPaggConfigProvider;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if($this->mundiPaggConfigProvider->getModuleStatus() == 0 ) {

            if (
                ($observer->getEvent()->getMethodInstance()->getCode() == "mundipagg_billet") ||
                ($observer->getEvent()->getMethodInstance()->getCode() == "mundipagg_creditcard") ||
                ($observer->getEvent()->getMethodInstance()->getCode() == "mundipagg_billet_creditcard") ||
                ($observer->getEvent()->getMethodInstance()->getCode() == "mundipagg_two_creditcard")
            ) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }

    }
}