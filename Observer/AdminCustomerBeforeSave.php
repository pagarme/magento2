<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Pagarme\Core\Payment\Services\CustomerService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformCustomerDecorator;
use Pagarme\Pagarme\Helper\CustomerUpdatePagarmeHelper;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\Phone;

class AdminCustomerBeforeSave implements ObserverInterface
{
    /**
     * @var CustomerUpdatePagarmeHelper
     */
    protected $customerUpdatePagarmeHelper;

    /**
     * AdminCustomerBeforeSave constructor.
     * @param CustomerUpdatePagarmeHelper $customerUpdatePagarmeHelper
     * @throws \Exception
     */
    public function __construct(
        CustomerUpdatePagarmeHelper $customerUpdatePagarmeHelper
    )
    {
        $this->customerUpdatePagarmeHelper = $customerUpdatePagarmeHelper;
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
        $customer = $event->getCustomer();
        $customer->setData('phones', $this->getPhoneNumbers($event->getRequest()));
        $platformCustomer = new Magento2PlatformCustomerDecorator($customer);
        if (empty($platformCustomer->getPagarmeId())) {
            return $this;
        }

        $customerService = new CustomerService();
        try {
            $customerService->updateCustomerAtPagarme($platformCustomer);
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

        /* @var PagarmeConfigProvider $pagarmeProvider */
        $pagarmeProvider = $objectManager->get(PagarmeConfigProvider::class);

        return $pagarmeProvider->getModuleStatus();
    }


    private function getPhoneNumbers($request)
    {
        $shippingPhone = $request->getParam('default_shipping_address')['telephone'];
        $billingPhone = $request->getParam('default_billing_address')['telephone'];
        $shippingPhone = new Phone($shippingPhone);
        $billingPhone = new Phone($billingPhone);
        $phonesArray = [$shippingPhone, $billingPhone];
        return CustomerPhones::create($phonesArray);
    }
}
