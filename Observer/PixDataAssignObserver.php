<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\Cards;
use Pagarme\Pagarme\Model\CardsRepository;

class PixDataAssignObserver extends AbstractDataAssignObserver
{
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $info = $method->getInfoInstance();
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $info->setAdditionalInformation('pix_buyer_checkbox', $additionalData->getBilletBuyerCheckbox());
        $info->setAdditionalInformation('pix_buyer_name', $additionalData->getBilletBuyerName());
        $info->setAdditionalInformation('pix_buyer_email', $additionalData->getBilletBuyerEmail());
        $info->setAdditionalInformation('pix_buyer_document', $additionalData->getBilletBuyerDocument());
        $info->setAdditionalInformation('pix_buyer_street_title', $additionalData->getBilletBuyerStreetTitle());
        $info->setAdditionalInformation('pix_buyer_street_number', $additionalData->getBilletBuyerStreetNumber());
        $info->setAdditionalInformation('pix_buyer_street_complement', $additionalData->getBilletBuyerStreetComplement());
        $info->setAdditionalInformation('pix_buyer_zipcode', $additionalData->getBilletBuyerZipcode());
        $info->setAdditionalInformation('pix_buyer_neighborhood', $additionalData->getBilletBuyerNeighborhood());
        $info->setAdditionalInformation('pix_buyer_city', $additionalData->getBilletBuyerCity());
        $info->setAdditionalInformation('pix_buyer_state', $additionalData->getBilletBuyerState());
        $info->setAdditionalInformation('pix_buyer_home_phone', $additionalData->getBilletBuyerHomePhone());
        $info->setAdditionalInformation('pix_buyer_mobile_phone', $additionalData->getBilletBuyerMobilePhone());

        return $this;
    }
}
