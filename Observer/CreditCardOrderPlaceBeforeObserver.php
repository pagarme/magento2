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
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Event\ObserverInterface;
use MundiPagg\MundiPagg\Api\InstallmentsByBrandManagementInterface;

class CreditCardOrderPlaceBeforeObserver implements ObserverInterface
{
    protected $installmentsInterface;

    /**
     * @param InstallmentsByBrandManagementInterface $installmentsInterface
     */
    public function __construct(
        InstallmentsByBrandManagementInterface $installmentsInterface
    )
    {
        $this->setInstallmentsInterface($installmentsInterface);
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if ('mundipagg_creditcard' != $payment->getMethod() || 'mundipagg_billet_creditcard' != $payment->getMethod()) {
            return $this;
        }

        $tax = $this->getTaxOrder($payment->getAdditionalInformation('cc_installments'), $payment->getCcType());
        $total = $order->getGrandTotal() + $tax;
        $order->setTaxAmount($tax)->setBaseTaxAmount($tax)->setBaseGrandTotal($total)->setGrandTotal($total);
        
        return $this;
    }

    protected function getTaxOrder($installments, $type = null)
    {
        $returnInstallments = $this->getInstallmentsInterface()->getInstallmentsByBrand($type);
        $result = 0;

        foreach ($returnInstallments as $installment) {
            if ($installment['id'] == $installments) {
                $result = $installment['interest'];
                break;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getInstallmentsInterface()
    {
        return $this->installmentsInterface;
    }

    /**
     * @param mixed $installmentsInterface
     *
     * @return self
     */
    public function setInstallmentsInterface($installmentsInterface)
    {
        $this->installmentsInterface = $installmentsInterface;

        return $this;
    }
}
