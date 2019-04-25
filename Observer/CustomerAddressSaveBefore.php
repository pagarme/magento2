<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\InputException;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class CustomerAddressSaveBefore implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $customerAddress = $observer->getCustomerAddress();

        $this->addressValidation($customerAddress);

        return $this;
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);

        return $mundipaggProvider->getModuleStatus();
    }

    public function getModuleAddressConfig()
    {
        $objectManager = ObjectManager::getInstance();
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);
        return $mundipaggProvider->getCustomerAddressConfiguration();
    }

    private function filterAddressIndexes($addressConfig)
    {
        $addressIndexes = [];

        foreach ($addressConfig as $key => $value) {
            if (preg_match('/street_\w{1}$/', $value) > 0) {
                $addressIndexes[$key] = explode('street_', $value)[1];
            }
        }

        return $addressIndexes;
    }

    /**
     * @param $customerAddress
     * @throws InputException
     */
    public function addressValidation($customerAddress)
    {
        $allStreetLines = $customerAddress->getStreet();

        $addressIndexes =
            $this->filterAddressIndexes(
                $this->getModuleAddressConfig()
            );

        if($addressIndexes) {
            if(empty($customerAddress->getStreetLine($addressIndexes['street']))){
                throw new InputException(__("Please check your address. Street Address field (Street) is required."));
            }

            if(empty($customerAddress->getStreetLine($addressIndexes['number']))){
                throw new InputException(__("Please check your address. Street Address field (Number) is required."));
            }
        }

        if (!is_array($allStreetLines) || count($allStreetLines) < 3) {

            Magento2CoreSetup::bootstrap();

            $i18n = new LocalizationService();
            $message = "Invalid address. Please fill the street lines and try again.";
            $ExceptionMessage = $i18n->getDashboard($message);
            $incorrectAddress = json_encode($allStreetLines, JSON_PRETTY_PRINT);
            $ExceptionMessage .= ' ' . $incorrectAddress;

            $e = new \Exception($ExceptionMessage );
            $log = new LogService('Order', true);
            $log->exception($e);

            //Magento accepts only Phrase() exceptions in this case
            throw new InputException(__($message));
        }
    }
}
