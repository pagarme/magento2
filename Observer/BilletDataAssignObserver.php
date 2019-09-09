<?php
/**
 * Class CreditCardDataAssignObserver
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Observer;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\Cards;
use MundiPagg\MundiPagg\Model\CardsRepository;

class BilletDataAssignObserver extends AbstractDataAssignObserver
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

        $info->setAdditionalInformation('billet_buyer_checkbox', $additionalData->getBilletBuyerCheckbox());
        $info->setAdditionalInformation('billet_buyer_name', $additionalData->getBilletBuyerName());
        $info->setAdditionalInformation('billet_buyer_email', $additionalData->getBilletBuyerEmail());
        $info->setAdditionalInformation('billet_buyer_document', $additionalData->getBilletBuyerDocument());
        $info->setAdditionalInformation('billet_buyer_street_title', $additionalData->getBilletBuyerStreetTitle());
        $info->setAdditionalInformation('billet_buyer_street_number', $additionalData->getBilletBuyerStreetNumber());
        $info->setAdditionalInformation('billet_buyer_street_complement', $additionalData->getBilletBuyerStreetComplement());
        $info->setAdditionalInformation('billet_buyer_zipcode', $additionalData->getBilletBuyerZipcode());
        $info->setAdditionalInformation('billet_buyer_neighborhood', $additionalData->getBilletBuyerNeighborhood());
        $info->setAdditionalInformation('billet_buyer_city', $additionalData->getBilletBuyerCity());
        $info->setAdditionalInformation('billet_buyer_state', $additionalData->getBilletBuyerState());

        return $this;
    }
}
