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


use Magento\Framework\DataObject;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;

class TwoCreditCardDataAssignObserver extends AbstractDataAssignObserver
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

        $info->setAdditionalInformation('cc_saved_card_first', '0');
        $info->setAdditionalInformation('cc_saved_card_second', '0');

        if ($additionalData->getCcSavedCardFirst()) {
            $info->setAdditionalInformation('cc_saved_card_first', $additionalData->getCcSavedCardFirst());
            $info->setAdditionalInformation('cc_first_card_amount', $additionalData->getCcFirstCardAmount());
            $info->setAdditionalInformation('cc_first_card_tax_amount', $additionalData->getCcFirstCardTaxAmount());
            $info->setAdditionalInformation('cc_type_first', $additionalData->getCcTypeFirst());
            $info->setAdditionalInformation('cc_last_4_first', $additionalData->getCcLast4First());
            $info->addData([
                'cc_first_card_amount' => $additionalData->getCcFirstCardAmount(),
                'cc_type_first' => $additionalData->getCcTypeFirst()
            ]);
        } else {

            $info->setAdditionalInformation('cc_first_card_amount', $additionalData->getCcFirstCardAmount());
            $info->setAdditionalInformation('cc_first_card_tax_amount', $additionalData->getCcFirstCardTaxAmount());
            $info->setAdditionalInformation('cc_type_first', $additionalData->getCcTypeFirst());
            $info->setAdditionalInformation('cc_last_4_first', substr($additionalData->getCcNumberFirst(), -4));

            $info->addData([
                'cc_first_card_amount' => $additionalData->getCcFirstCardAmount(),
                'cc_type_first' => $additionalData->getCcTypeFirst(),
                'cc_owner_first' => $additionalData->getCcOwnerFirst(),
                'cc_number_first' => $additionalData->getCcNumberFirst(),
                'cc_last_4_first' => substr($additionalData->getCcNumberFirst(), -4),
                'cc_cid_first' => $additionalData->getCcCidFirst(),
                'cc_exp_month_first' => $additionalData->getCcExpMonthFirst(),
                'cc_exp_year_first' => $additionalData->getCcExpYearFirst(),
            ]);

            $info->setAdditionalInformation('cc_savecard_first', $additionalData->getCcSavecardFirst());
        }

        if ($additionalData->getCcSavedCardSecond()) {

            $info->setAdditionalInformation('cc_saved_card_second', $additionalData->getCcSavedCardSecond());
            $info->setAdditionalInformation('cc_second_card_amount', $additionalData->getCcSecondCardAmount());
            $info->setAdditionalInformation('cc_second_card_tax_amount', $additionalData->getCcSecondCardTaxAmount());
            $info->setAdditionalInformation('cc_type_second', $additionalData->getCcTypeSecond());
            $info->setAdditionalInformation('cc_last_4_second', $additionalData->getCcLast4Second());
            $info->addData([
                'cc_second_card_amount' => $additionalData->getCcSecondCardAmount(),
                'cc_type_second' => $additionalData->getCcTypeSecond()
            ]);
        } else {

            $info->setAdditionalInformation('cc_second_card_amount', $additionalData->getCcSecondCardAmount());
            $info->setAdditionalInformation('cc_second_card_tax_amount', $additionalData->getCcSecondCardTaxAmount());
            $info->setAdditionalInformation('cc_type_second', $additionalData->getCcTypeSecond());
            $info->setAdditionalInformation('cc_last_4_second', substr($additionalData->getCcNumberSecond(), -4));

            $info->addData([
                'cc_second_card_amount' => $additionalData->getCcSecondCardAmount(),
                'cc_type_second' => $additionalData->getCcTypeSecond(),
                'cc_owner_second' => $additionalData->getCcOwnerSecond(),
                'cc_number_second' => $additionalData->getCcNumberSecond(),
                'cc_last_4_second' => substr($additionalData->getCcNumberSecond(), -4),
                'cc_cid_second' => $additionalData->getCcCidSecond(),
                'cc_exp_month_second' => $additionalData->getCcExpMonthSecond(),
                'cc_exp_year_second' => $additionalData->getCcExpYearSecond(),
            ]);

            $info->setAdditionalInformation('cc_savecard_second', $additionalData->getCcSavecardSecond());
        }

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
}
