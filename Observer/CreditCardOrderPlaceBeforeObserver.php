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
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Core\Kernel\Services\InstallmentService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Pagarme\Api\InstallmentsByBrandManagementInterface;
use Pagarme\Pagarme\Api\InstallmentsByBrandAndAmountManagementInterface;


class CreditCardOrderPlaceBeforeObserver implements ObserverInterface
{
    protected $installmentsInterface;
    protected $installmentsByBrandAndAmountInterface;

    /**
     * @param InstallmentsByBrandManagementInterface $installmentsInterface
     */
    public function __construct(
        InstallmentsByBrandManagementInterface $installmentsInterface,
        InstallmentsByBrandAndAmountManagementInterface $installmentsByBrandAndAmountInterface
    )
    {
        $this->setInstallmentsInterface($installmentsInterface);
        $this->setInstallmentsByBrandAndAmountInterface($installmentsByBrandAndAmountInterface);
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if ('pagarme_creditcard' != $payment->getMethod() && 'pagarme_billet_creditcard' != $payment->getMethod() && 'pagarme_two_creditcard' != $payment->getMethod()) {
            return $this;
        }

        if($payment->getMethod() == 'pagarme_creditcard'){
            $tax = $this->getTaxOrder(
                $payment->getAdditionalInformation('cc_installments'),
                $payment->getAdditionalInformation('cc_type'),
                $order
            );
        }

        if($payment->getMethod() == 'pagarme_billet_creditcard'){
            $tax = $this->getTaxOrderByAmount($payment->getAdditionalInformation('cc_installments'), $payment->getCcType(), $payment->getAdditionalInformation('cc_cc_amount'));
        }

        if($payment->getMethod() == 'pagarme_two_creditcard'){
            $tax = $payment->getAdditionalInformation('cc_second_card_tax_amount') + $payment->getAdditionalInformation('cc_first_card_tax_amount');
        }


        $total = $order->getGrandTotal() + $tax;
        $order->setTaxAmount($tax)->setBaseTaxAmount($tax)->setBaseGrandTotal($total)->setGrandTotal($total);

        return $this;
    }

    protected function getTaxOrder($installments, $type = null, $order)
    {
        $installmentService = new InstallmentService();

        $brand = CardBrand::$type();

        $grandTotal = number_format((float)$order->getGrandTotal(), 2, '.', '');
        $returnInstallments = $installmentService->getInstallmentsFor(
            null,
            $brand,
            $grandTotal * 100
        );

        $result = 0;

        foreach ($returnInstallments as $installment) {
            if ($installment->getTimes() == $installments) {
                $result =
                    (int) ($installment->getTotal() - $installment->getBaseTotal());
                $result = $result / 100;
                break;
            }
        }

        return $result;
    }

    protected function getTaxOrderByAmount($installments, $type = null, $amount)
    {
        $returnInstallments = $this->getInstallmentsByBrandAndAmountInterface()->getInstallmentsByBrandAndAmount($type,$amount);
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

    /**
     * @return mixed
     */
    public function getInstallmentsByBrandAndAmountInterface()
    {
        return $this->installmentsByBrandAndAmountInterface;
    }

    /**
     * @param mixed $installmentsByBrandAndAmountInterface
     *
     * @return self
     */
    public function setInstallmentsByBrandAndAmountInterface($installmentsByBrandAndAmountInterface)
    {
        $this->installmentsByBrandAndAmountInterface = $installmentsByBrandAndAmountInterface;

        return $this;
    }
}
