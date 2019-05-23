<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Mundipagg\Core\Payment\Services\CustomerService;
use Mundipagg\Core\Kernel\Services\LogService;
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
        try {
            $customerService->updateCustomerAtMundipagg($platformCustomer);
        } catch (\Exception $exception) {
            $log = new LogService('CustomerService');
            $log->info($exception->getMessage());
            $log->info(print_r($exception->errors, true));

            if ($exception->getCode() == 404) {
                $log->info("Deleting customer {$platformCustomer->getCode()} on core table");
                $customerService->deleteCustomerOnPlatform($platformCustomer);
            }
        }
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);

        return $mundipaggProvider->getModuleStatus();
    }
}
