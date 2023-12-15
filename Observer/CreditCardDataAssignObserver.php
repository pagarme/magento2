<?php
/**
 * Class CreditCardDataAssignObserver
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Observer;


use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Pagarme\Pagarme\Helper\MultiBuyerDataAssign;
use Pagarme\Pagarme\Model\CardsRepository;

class CreditCardDataAssignObserver extends AbstractDataAssignObserver
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

        $this->fillCardData($additionalData, $info);

        $multiBuyerDataAssign = new MultiBuyerDataAssign();
        $multiBuyerDataAssign->setCcMultiBuyer($info, $additionalData);

        $info->setAdditionalInformation('cc_installments', 1);

        if ($additionalData->getCcInstallments()) {
            $info->setAdditionalInformation(
                'cc_installments',
                (int) $additionalData->getCcInstallments()
            );
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $additionalData
     * @param mixed $info
     * @return void
     */
    public function fillCardData(DataObject $additionalData, $info)
    {
        if ($additionalData->getCcSavedCard()) {
              $this->fillSavedCardData($additionalData, $info);
              return;
        }
        $this->fillNotSavedCardData($info, $additionalData);
    }

    /**
     * @param DataObject $additionalData
     * @param $info
     */
    private function fillSavedCardData(DataObject $additionalData, $info)
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

    /**
     * @param $info
     * @param DataObject $additionalData
     */
    private function fillNotSavedCardData($info, DataObject $additionalData)
    {
        $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
        $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
        $info->setAdditionalInformation('authentication', $additionalData->getAuthentication());
        if ($additionalData->getCcLast4()) {
            $info->setAdditionalInformation(
                'cc_last_4',
                substr($additionalData->getCcLast4(),-4)
            );
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

}
