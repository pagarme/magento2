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
use Pagarme\Pagarme\Helper\MultiBuyerDataAssign;
use Pagarme\Pagarme\Model\Cards;
use Pagarme\Pagarme\Model\CardsRepository;

class DebitDataAssignObserver extends AbstractDataAssignObserver
{
    private $cardsRepository;

    /**
     * CreditCardDataAssignObserver constructor.
     * @param CardsRepository $cardsRepository
     */
    public function __construct(
        CardsRepository $cardsRepository
    )
    {
        $this->cardsRepository = $cardsRepository;
    }

    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $info = $method->getInfoInstance();
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        if ($additionalData->getCcSavedCard() === 'new') {
            $additionalData->setCcSavedCard('');
        }

        $info->setAdditionalInformation('cc_saved_card', '0');
        $info->setAdditionalInformation('cc_installments', 1);

        $multiBuyerDataAssign = new MultiBuyerDataAssign();
        $multiBuyerDataAssign->setCcMultiBuyer($info, $additionalData);

        if ($additionalData->getCcSavedCard()) {
            $this->setSavedCardAdditionalData($info, $additionalData);
            return $this;
        }

        $this->setNewCardAdditionalData($info, $additionalData);
        return $this;
    }

    protected function setNewCardAdditionalData($info, $additionalData)
    {
        $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
        $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
        $info->setAdditionalInformation('authentication', $additionalData->getAuthentication());
        if ($additionalData->getCcLast4()) {
            $info->setAdditionalInformation('cc_last_4', substr($additionalData->getCcLast4(),-4));
        }
        $info->setAdditionalInformation('cc_token_credit_card', $additionalData->getCcTokenCreditCard());
        $info->addData([
            'cc_type' => $additionalData->getCcType(),
            'cc_owner' => $additionalData->getCcOwner(),
            'cc_last_4' => $additionalData->getCcLast4(),
            'cc_exp_month' => $additionalData->getCcExpMonth(),
            'cc_exp_year' => $additionalData->getCcExpYear(),
            'cc_token_credit_card' => $additionalData->getCcTokenCreditCard(),
        ]);

        $info->setAdditionalInformation('cc_savecard', $additionalData->getCcSavecard());
    }

    protected function setSavedCardAdditionalData($info, $additionalData)
    {
        $cardId = $additionalData->getCcSavedCard();
        $card = $this->cardsRepository->getById($cardId);

        $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
        $info->setAdditionalInformation('cc_type', $card->getBrand());
        $info->setAdditionalInformation(
            'cc_last_4',
            (string) $card->getLastFourNumbers()
        );
        $info->addData([
            'cc_type' => $card->getBrand(),
            'cc_owner' => $card->getCardHolderName(),
            'cc_last_4' => (string) $card->getLastFourNumbers()
        ]);
    }

}
