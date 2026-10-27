<?php
/**
 * Class CreditCardOrderPlaceBeforeObserver
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Pagarme\Pagarme\Service\Payment\ClientPaymentAmountValidator;
use Pagarme\Pagarme\Service\Payment\InstallmentInterestCalculator;

class CreditCardOrderPlaceBeforeObserver implements ObserverInterface
{
    const CREDIT_CARD_METHODS = [
        'pagarme_creditcard',
        'pagarme_billet_creditcard',
        'pagarme_two_creditcard',
    ];

    /** @var InstallmentInterestCalculator */
    private $interestCalculator;

    /** @var ClientPaymentAmountValidator */
    private $amountValidator;

    public function __construct(
        InstallmentInterestCalculator $interestCalculator,
        ClientPaymentAmountValidator $amountValidator
    ) {
        $this->interestCalculator = $interestCalculator;
        $this->amountValidator = $amountValidator;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if (!in_array($payment->getMethod(), self::CREDIT_CARD_METHODS, true)) {
            return $this;
        }

        $grandTotalInCents = (int) round($order->getGrandTotal() * 100);
        $interestInCents = $this->calculateInterestInCents($payment, $grandTotalInCents);

        $tax = $interestInCents / 100;
        $total = $order->getGrandTotal() + $tax;
        $order->setTaxAmount($tax)
            ->setBaseTaxAmount($tax)
            ->setBaseGrandTotal($total)
            ->setGrandTotal($total);

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    private function calculateInterestInCents($payment, $grandTotalInCents)
    {
        switch ($payment->getMethod()) {
            case 'pagarme_creditcard':
                return $this->singleCardInterestInCents($payment, $grandTotalInCents);
            case 'pagarme_billet_creditcard':
                return $this->billetCardInterestInCents($payment, $grandTotalInCents);
            case 'pagarme_two_creditcard':
                return $this->twoCardsInterestInCents($payment, $grandTotalInCents);
            default:
                throw new \LogicException(
                    'No interest calculation defined for payment method ' . $payment->getMethod()
                );
        }
    }

    private function singleCardInterestInCents($payment, $grandTotalInCents)
    {
        return $this->validatedInterestInCents(
            $payment->getAdditionalInformation('cc_type'),
            $payment->getAdditionalInformation('cc_installments'),
            $grandTotalInCents,
            $payment->getAdditionalInformation('cc_card_tax_amount')
        );
    }

    private function billetCardInterestInCents($payment, $grandTotalInCents)
    {
        $cardAmount = $this->amountValidator->parseMaskedAmountToCents(
            $payment->getAdditionalInformation('cc_cc_amount')
        );
        $billetAmount = $this->amountValidator->parseMaskedAmountToCents(
            $payment->getAdditionalInformation('cc_billet_amount')
        );
        $this->amountValidator->assertSplitMatchesTotal($grandTotalInCents, [$cardAmount, $billetAmount]);

        return $this->validatedInterestInCents(
            $payment->getAdditionalInformation('cc_type'),
            $payment->getAdditionalInformation('cc_installments'),
            $cardAmount,
            $payment->getAdditionalInformation('cc_cc_tax_amount')
        );
    }

    private function twoCardsInterestInCents($payment, $grandTotalInCents)
    {
        $firstAmount = $this->amountValidator->parseMaskedAmountToCents(
            $payment->getAdditionalInformation('cc_first_card_amount')
        );
        $secondAmount = $this->amountValidator->parseMaskedAmountToCents(
            $payment->getAdditionalInformation('cc_second_card_amount')
        );
        $this->amountValidator->assertSplitMatchesTotal($grandTotalInCents, [$firstAmount, $secondAmount]);

        return $this->validatedInterestInCents(
            $payment->getAdditionalInformation('cc_type_first'),
            $payment->getAdditionalInformation('cc_installments_first'),
            $firstAmount,
            $payment->getAdditionalInformation('cc_first_card_tax_amount')
        ) + $this->validatedInterestInCents(
            $payment->getAdditionalInformation('cc_type_second'),
            $payment->getAdditionalInformation('cc_installments_second'),
            $secondAmount,
            $payment->getAdditionalInformation('cc_second_card_tax_amount')
        );
    }

    /**
     * @throws LocalizedException
     */
    private function validatedInterestInCents($brand, $installments, $baseAmountInCents, $clientInterest)
    {
        $interestInCents = $this->interestCalculator->calculateInterestInCents(
            $brand,
            $installments,
            $baseAmountInCents
        );
        $this->amountValidator->assertInterestMatches($interestInCents, $clientInterest);

        return $interestInCents;
    }
}
