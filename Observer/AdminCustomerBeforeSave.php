<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Mundipagg\Core\Payment\Services\CustomerService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformCustomerDecorator;
use MundiPagg\MundiPagg\Helper\CustomerUpdateMundipaggHelper;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;
use Magento\Framework\App\ObjectManager;

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
        Magento2CoreSetup::bootstrap();
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $event = $observer->getEvent();

        $platformCustomer = new Magento2PlatformCustomerDecorator(
            $event->getCustomer()
        );

        $customerService = new CustomerService();
        $customerService->updateCustomerAtMundipagg($platformCustomer);
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);

        return $mundipaggProvider->getModuleStatus();
    }
}
