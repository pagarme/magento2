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
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\ConfigInterface;
use Pagarme\Pagarme\Helper\MultiBuyerDataAssign;
use Pagarme\Pagarme\Model\CardsRepository;
use Pagarme\Pagarme\Model\Enum\TdsModeEnum;
use Pagarme\Pagarme\Model\Enum\TdsReasonEnum;

class CreditCardDataAssignObserver extends AbstractDataAssignObserver
{
    private $cardsRepository;
    private $creditCardConfig;

    /**
     * CreditCardDataAssignObserver constructor.
     * @param CardsRepository $cardsRepository
     * @param ConfigInterface $creditCardConfig
     */
    public function __construct(
        CardsRepository $cardsRepository,
        ConfigInterface $creditCardConfig
    )
    {
        $this->cardsRepository = $cardsRepository;
        $this->creditCardConfig = $creditCardConfig;
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
     * @throws PaymentException
     */
    private function fillNotSavedCardData($info, DataObject $additionalData)
    {
        $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
        $info->setAdditionalInformation('cc_type', $additionalData->getCcType());

        $authentication = $additionalData->getAuthentication();
        $info->setAdditionalInformation('authentication', $authentication);

        $tdsReason = $this->determineTdsReason($authentication, $additionalData->getTdsReason());
        $info->setAdditionalInformation('3ds_reason', $tdsReason);

        if ($tdsReason === TdsReasonEnum::NOT_ELIGIBLE
            && $this->creditCardConfig->getTdsMode() === TdsModeEnum::MANDATORY
        ) {
            throw new PaymentException(
                __('Seu banco não suporta autenticação 3DS obrigatória neste momento. Utilize outro cartão ou método de pagamento.')
            );
        }

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

    /**
     * @param string|null $authentication
     * @param string|null $frontendReason
     * @return string
     */
    private function determineTdsReason($authentication, $frontendReason)
    {
        if (empty($authentication)) {
            return $frontendReason ?: TdsReasonEnum::UNKNOWN;
        }

        $authArray = is_array($authentication)
            ? $authentication
            : json_decode($authentication, true);

        $status = isset($authArray['trans_status']) ? $authArray['trans_status'] : null;

        if (in_array($status, AuthenticationStatusEnum::doesNotNeedToUseAntifraudStatuses())) {
            return TdsReasonEnum::AUTHORIZED;
        }

        return empty($status) ? TdsReasonEnum::UNKNOWN : TdsReasonEnum::DECLINED;
    }

}
