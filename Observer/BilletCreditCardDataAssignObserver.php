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
use Pagarme\Pagarme\Helper\MultiBuyerDataAssign;
use Pagarme\Pagarme\Model\Cards;
use Pagarme\Pagarme\Model\CardsRepository;

class BilletCreditCardDataAssignObserver extends AbstractDataAssignObserver
{
    private $cardsRepository;

    /**
     * BilletCreditCardDataAssignObserver constructor.
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

        if ($additionalData->getCcSavedCard()) {
            $cardId = $additionalData->getCcSavedCard();
            $card = $this->cardsRepository->getById($cardId);

            $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
            $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
            $info->setAdditionalInformation('cc_cc_tax_amount', $additionalData->getCcCcTaxAmount());
            $info->setAdditionalInformation('cc_type', $card->getBrand());
            $info->setAdditionalInformation(
                'cc_last_4',
                (string) $card->getLastFourNumbers()
            );

            $info->addData([
                'cc_cc_amount' => $additionalData->getCcCcAmount(),
                'cc_billet_amount' => $additionalData->getCcBilletAmount()
            ]);
        }else{

            $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
            $info->setAdditionalInformation('cc_cc_tax_amount', $additionalData->getCcCcTaxAmount());
            $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
            $info->setAdditionalInformation('cc_last_4', $additionalData->getCcLast4());
            $info->setAdditionalInformation('cc_token_credit_card', $additionalData->getCcTokenCreditCard());

            $info->addData([
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => $additionalData->getCcLast4(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_token_credit_card' => $additionalData->getCcTokenCreditCard(),
                'cc_cc_amount' => $additionalData->getCcCcAmount(),
                'cc_billet_amount' => $additionalData->getCcBilletAmount()
            ]);

            $info->setAdditionalInformation('cc_savecard', $additionalData->getCcSavecard());
        }
      
        $multiBuyerDataAssign = new MultiBuyerDataAssign();
        $multiBuyerDataAssign->setCcMultiBuyer($info, $additionalData);
        $multiBuyerDataAssign->setBilletMultiBuyer($info, $additionalData);

        $info->setAdditionalInformation('cc_installments', 1);
        $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
        $info->setAdditionalInformation('cc_billet_amount', $additionalData->getCcBilletAmount());

        if ($additionalData->getCcInstallments()) {
            $info->setAdditionalInformation('cc_installments', (int) $additionalData->getCcInstallments());
        }

        return $this;
    }

}
