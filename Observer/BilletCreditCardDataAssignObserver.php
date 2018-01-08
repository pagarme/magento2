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

class BilletCreditCardDataAssignObserver extends AbstractDataAssignObserver
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

        $info->setAdditionalInformation('cc_saved_card', '0');

        if ($additionalData->getCcSavedCard()) {
            $info->setAdditionalInformation('cc_saved_card', $additionalData->getCcSavedCard());
            $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
            $info->setAdditionalInformation('cc_cc_tax_amount', $additionalData->getCcCcTaxAmount());
            $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
            $info->setAdditionalInformation('cc_last_4', $additionalData->getCcLast4());

            $info->addData([
                'cc_cc_amount' => $additionalData->getCcCcAmount(),
                'cc_billet_amount' => $additionalData->getCcBilletAmount()
            ]);
        }else{

            $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
            $info->setAdditionalInformation('cc_cc_tax_amount', $additionalData->getCcCcTaxAmount());
            $info->setAdditionalInformation('cc_type', $additionalData->getCcType());
            $info->setAdditionalInformation('cc_last_4', substr($additionalData->getCcLast4(),-4));

            $info->addData([
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_cid' => $additionalData->getCcCid(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_cc_amount' => $additionalData->getCcCcAmount(),
                'cc_billet_amount' => $additionalData->getCcBilletAmount()
            ]);
            
            $info->setAdditionalInformation('cc_savecard', $additionalData->getCcSavecard());
        }

        $info->setAdditionalInformation('cc_installments', 1);
        $info->setAdditionalInformation('cc_cc_amount', $additionalData->getCcCcAmount());
        $info->setAdditionalInformation('cc_billet_amount', $additionalData->getCcBilletAmount());

        if ($additionalData->getCcInstallments()) {
            $info->setAdditionalInformation('cc_installments', (int) $additionalData->getCcInstallments());
        }

        return $this;
    }
}
