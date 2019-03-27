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
use Magento\GiftMessage\Model\Save;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\Cards;
use MundiPagg\MundiPagg\Model\CardsRepository;
use Zend\Form\Annotation\Object;

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

        $info->setAdditionalInformation('cc_saved_card', '0');

        if ($additionalData->getCcSavedCard()) {
            $cardId = $additionalData->getCcSavedCard();
            $card = null;
            try {
                $card = $this->cardsRepository->getById($cardId);
            } catch (NoSuchEntityException $e) {
            }

            if ($card === null) {
                Magento2CoreSetup::bootstrap();

                $savedCardRepository = new SavedCardRepository();

                $matchIds = [];
                preg_match('/mp_core_\d/', $cardId, $matchIds);

                if (isset($matchIds[0])) {
                    $savedCardId = preg_replace('/\D/', '', $matchIds[0]);
                    $savedCard = $savedCardRepository->find($savedCardId);
                    if ($savedCard !== null) {
                        $objectManager = ObjectManager::getInstance();
                        /** @var Cards $card */
                        $card = $objectManager->get(Cards::class);
                        $card->setBrand($savedCard->getBrand()->getName());
                        $card->setLastFourNumbers($savedCard->getLastFourDigits()->getValue());
                    }
                }
            }

            if ($card === null) {
                throw new NoSuchEntityException(__('Cards with id "%1" does not exist.', $cardId));
            }

            $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
            $info->setAdditionalInformation('cc_type', $card->getBrand());
            $info->setAdditionalInformation('cc_last_4', $card->getLastFourNumbers());
        }else{
            $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
            $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
            $info->setAdditionalInformation('cc_last_4', substr($additionalData->getCcLast4(),-4));
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

        $info->setAdditionalInformation('cc_installments', 1);

        if ($additionalData->getCcInstallments()) {
            $info->setAdditionalInformation('cc_installments', (int) $additionalData->getCcInstallments());
        }

        return $this;
    }
}
