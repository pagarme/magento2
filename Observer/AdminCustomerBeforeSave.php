<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Mundipagg\Core\Payment\Services\CustomerService;
use Mundipagg\Core\Kernel\Services\LogService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformCustomerDecorator;
use MundiPagg\MundiPagg\Helper\CustomerUpdateMundipaggHelper;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;

class AdminCustomerBeforeSave implements ObserverInterface
{
    /**
     * @var CustomerUpdateMundipaggHelper
     */
    protected $customerUpdateMundipaggHelper;

    /**
     * AdminCustomerBeforeSave constructor.
     * @param CustomerUpdateMundipaggHelper $customerUpdateMundipaggHelper
     * @throws \Exception
     */
    public function __construct(
        CustomerUpdateMundipaggHelper $customerUpdateMundipaggHelper
    )
    {
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
        $platformCustomer = new Magento2PlatformCustomerDecorator($event->getCustomer());

        $customerService = new CustomerService();
        try {
            $customerService->updateCustomerAtMundipagg($platformCustomer);
        } catch (\Exception $exception) {
            $log = new LogService('CustomerService');
            $log->info($exception->getMessage());

            if ($exception->getCode() == 404) {
                $log->info(
                    "Deleting customer {$platformCustomer->getCode()} on core table"
                );

                $customerService->deleteCustomerOnPlatform($platformCustomer);
            }

            throw new InputException(__($exception->getMessage()));
        }
    }

    /**
     * @return string
     */
    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();

        /* @var MundiPaggConfigProvider $mundipaggProvider */
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);

        return $mundipaggProvider->getModuleStatus();
    }
}
