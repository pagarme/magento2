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

class TwoCreditCardDataAssignObserver extends AbstractDataAssignObserver
{
    private $cardsRepository;

    /**
     * TwoCreditCardDataAssignObserver constructor.
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

        if ($additionalData->getCcSavedCardFirst() === 'new') {
            $additionalData->setCcSavedCardFirst('');
        }

        if ($additionalData->getCcSavedCardSecond() === 'new') {
            $additionalData->setCcSavedCardSecond('');
        }

        $info->setAdditionalInformation('cc_saved_card_first', '0');
        $info->setAdditionalInformation('cc_saved_card_second', '0');

        if ($additionalData->getCcSavedCardFirst()) {
            $cardId = $additionalData->getCcSavedCardFirst();
            $card = $this->cardsRepository->getById($cardId);

            $info->setAdditionalInformation('cc_saved_card_first', $additionalData->getCcSavedCardFirst());
            $info->setAdditionalInformation('cc_first_card_amount', $additionalData->getCcFirstCardAmount());
            $info->setAdditionalInformation('cc_first_card_tax_amount', $additionalData->getCcFirstCardTaxAmount());
            $info->setAdditionalInformation('cc_type_first', $card->getBrand());
            $info->setAdditionalInformation(
                'cc_last_4_first',
                (string) $card->getLastFourNumbers()
            );
            $info->addData([
                'cc_first_card_amount' => $additionalData->getCcFirstCardAmount(),
                'cc_type_first' => $additionalData->getCcTypeFirst()
            ]);
        } else {

            $info->setAdditionalInformation('cc_first_card_amount', $additionalData->getCcFirstCardAmount());
            $info->setAdditionalInformation('cc_first_card_tax_amount', $additionalData->getCcFirstCardTaxAmount());
            $info->setAdditionalInformation('cc_type_first', $additionalData->getCcTypeFirst());
            $info->setAdditionalInformation('cc_last_4_first', $additionalData->getCcLast4First());
            $info->setAdditionalInformation('cc_token_credit_card_first', $additionalData->getCcTokenCreditCardFirst());

            $info->addData([
                'cc_first_card_amount' => $additionalData->getCcFirstCardAmount(),
                'cc_type_first' => $additionalData->getCcTypeFirst(),
                'cc_owner_first' => $additionalData->getCcOwnerFirst(),
                'cc_last_4_first' => $additionalData->getCcLast4First(),
                'cc_token_credit_card_first' => $additionalData->getCcTokenCreditCardFirst(),
            ]);

            $info->setAdditionalInformation('cc_savecard_first', $additionalData->getCcSavecardFirst());
        }

        if ($additionalData->getCcSavedCardSecond()) {
            $cardId = $additionalData->getCcSavedCardSecond();
            $card = $this->cardsRepository->getById($cardId);

            $info->setAdditionalInformation('cc_saved_card_second', $additionalData->getCcSavedCardSecond());
            $info->setAdditionalInformation('cc_second_card_amount', $additionalData->getCcSecondCardAmount());
            $info->setAdditionalInformation('cc_second_card_tax_amount', $additionalData->getCcSecondCardTaxAmount());
            $info->setAdditionalInformation('cc_type_second', $card->getBrand());
            $info->setAdditionalInformation(
                'cc_last_4_second',
                (string) $card->getLastFourNumbers()
            );
            $info->addData([
                'cc_second_card_amount' => $additionalData->getCcSecondCardAmount(),
                'cc_type_second' => $additionalData->getCcTypeSecond()
            ]);
        } else {

            $info->setAdditionalInformation('cc_second_card_amount', $additionalData->getCcSecondCardAmount());
            $info->setAdditionalInformation('cc_second_card_tax_amount', $additionalData->getCcSecondCardTaxAmount());
            $info->setAdditionalInformation('cc_type_second', $additionalData->getCcTypeSecond());
            $info->setAdditionalInformation('cc_last_4_second', $additionalData->getCcLast4Second());
            $info->setAdditionalInformation('cc_token_credit_card_second', $additionalData->getCcTokenCreditCardSecond());

            $info->addData([
                'cc_second_card_amount' => $additionalData->getCcSecondCardAmount(),
                'cc_type_second' => $additionalData->getCcTypeSecond(),
                'cc_owner_second' => $additionalData->getCcOwnerSecond(),
                'cc_last_4_second' => $additionalData->getCcLast4Second(),
                'cc_token_credit_card_second' => $additionalData->getCcTokenCreditCardSecond(),
            ]);

            $info->setAdditionalInformation('cc_savecard_second', $additionalData->getCcSavecardSecond());
        }

        $this->setMultiBuyer($info, $additionalData);

        $info->setAdditionalInformation('cc_installments_first', 1);
        $info->setAdditionalInformation('cc_installments_second', 1);

        if ($additionalData->getCcInstallmentsFirst()) {
            $info->setAdditionalInformation('cc_installments_first', (int)$additionalData->getCcInstallmentsFirst());
        }

        if ($additionalData->getCcInstallmentsSecond()) {
            $info->setAdditionalInformation('cc_installments_second', (int)$additionalData->getCcInstallmentsSecond());
        }

        return $this;
    }

    /**
     * @param $info
     * @param $additionalData
     */
    protected function setMultiBuyer($info, $additionalData)
    {
        $info->setAdditionalInformation('cc_buyer_checkbox_first', $additionalData->getCcBuyerCheckboxFirst());
        if ($additionalData->getCcBuyerCheckboxFirst()) {
            $info->setAdditionalInformation('cc_buyer_name_first', $additionalData->getCcBuyerNameFirst());
            $info->setAdditionalInformation('cc_buyer_email_first', $additionalData->getCcBuyerEmailFirst());
            $info->setAdditionalInformation('cc_buyer_document_first', $additionalData->getCcBuyerDocumentFirst());
            $info->setAdditionalInformation('cc_buyer_street_title_first', $additionalData->getCcBuyerStreetTitleFirst());
            $info->setAdditionalInformation('cc_buyer_street_number_first', $additionalData->getCcBuyerStreetNumberFirst());
            $info->setAdditionalInformation('cc_buyer_street_complement_first', $additionalData->getCcBuyerStreetComplementFirst());
            $info->setAdditionalInformation('cc_buyer_zipcode_first', $additionalData->getCcBuyerZipcodeFirst());
            $info->setAdditionalInformation('cc_buyer_neighborhood_first', $additionalData->getCcBuyerNeighborhoodFirst());
            $info->setAdditionalInformation('cc_buyer_city_first', $additionalData->getCcBuyerCityFirst());
            $info->setAdditionalInformation('cc_buyer_state_first', $additionalData->getCcBuyerStateFirst());
            $info->setAdditionalInformation('cc_buyer_home_phone_first', $additionalData->getCcBuyerHomePhoneFirst());
            $info->setAdditionalInformation('cc_buyer_mobile_phone_first', $additionalData->getCcBuyerMobilePhoneFirst());
        }

        $info->setAdditionalInformation('cc_buyer_checkbox_second', $additionalData->getCcBuyerCheckboxSecond());
        if ($additionalData->getCcBuyerCheckboxSecond()) {
            $info->setAdditionalInformation('cc_buyer_name_second', $additionalData->getCcBuyerNameSecond());
            $info->setAdditionalInformation('cc_buyer_email_second', $additionalData->getCcBuyerEmailSecond());
            $info->setAdditionalInformation('cc_buyer_document_second', $additionalData->getCcBuyerDocumentSecond());
            $info->setAdditionalInformation('cc_buyer_street_title_second', $additionalData->getCcBuyerStreetTitleSecond());
            $info->setAdditionalInformation('cc_buyer_street_number_second', $additionalData->getCcBuyerStreetNumberSecond());
            $info->setAdditionalInformation('cc_buyer_street_complement_second', $additionalData->getCcBuyerStreetComplementSecond());
            $info->setAdditionalInformation('cc_buyer_zipcode_second', $additionalData->getCcBuyerZipcodeSecond());
            $info->setAdditionalInformation('cc_buyer_neighborhood_second', $additionalData->getCcBuyerNeighborhoodSecond());
            $info->setAdditionalInformation('cc_buyer_city_second', $additionalData->getCcBuyerCitySecond());
            $info->setAdditionalInformation('cc_buyer_state_second', $additionalData->getCcBuyerStateSecond());
            $info->setAdditionalInformation('cc_buyer_home_phone_second', $additionalData->getCcBuyerHomePhoneSecond());
            $info->setAdditionalInformation('cc_buyer_mobile_phone_second', $additionalData->getCcBuyerMobilePhoneSecond());
        }
    }
}
