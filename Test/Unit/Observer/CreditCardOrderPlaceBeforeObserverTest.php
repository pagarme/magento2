<?php

namespace Pagarme\Pagarme\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mockery;
use Pagarme\Pagarme\Observer\CreditCardOrderPlaceBeforeObserver;
use Pagarme\Pagarme\Service\Payment\ClientPaymentAmountValidator;
use Pagarme\Pagarme\Service\Payment\InstallmentInterestCalculator;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Observer\CreditCardOrderPlaceBeforeObserver
 */
class CreditCardOrderPlaceBeforeObserverTest extends BaseTest
{
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = Mockery::mock(InstallmentInterestCalculator::class);
    }

    private function execute($method, array $information, $grandTotal = 100.0)
    {
        $payment = Mockery::mock('Magento\Sales\Model\Order\Payment');
        $payment->shouldReceive('getMethod')->andReturn($method);
        $payment->shouldReceive('getAdditionalInformation')->andReturnUsing(
            function ($key) use ($information) {
                return $information[$key] ?? null;
            }
        );

        $order = new class($payment, $grandTotal) extends DataObject {
            public function __construct($payment, $grandTotal)
            {
                parent::__construct(['payment' => $payment, 'grand_total' => $grandTotal]);
            }

            public function getPayment()
            {
                return $this->getData('payment');
            }
        };

        $observer = new CreditCardOrderPlaceBeforeObserver($this->calculator, new ClientPaymentAmountValidator());
        $observer->execute(new Observer(['order' => $order]));

        return $order;
    }

    public function testIgnoresOtherPaymentMethods()
    {
        $this->calculator->shouldNotReceive('calculateInterestInCents');

        $order = $this->execute('pagarme_pix', []);

        $this->assertNull($order->getTaxAmount());
    }

    public function testSingleCardAppliesServerInterestToGrandTotal()
    {
        $this->calculator->shouldReceive('calculateInterestInCents')->with('visa', 3, 10000)->andReturn(199);

        $order = $this->execute('pagarme_creditcard', [
            'cc_type' => 'visa',
            'cc_installments' => 3,
            'cc_card_tax_amount' => '1.99',
        ]);

        $this->assertEqualsWithDelta(101.99, $order->getGrandTotal(), 0.001);
        $this->assertEqualsWithDelta(1.99, $order->getTaxAmount(), 0.001);
    }

    public function testUnknownCreditCardMethodIsIgnoredAndNotTreatedAsTwoCards()
    {
        $this->calculator->shouldNotReceive('calculateInterestInCents');

        $order = $this->execute('pagarme_unknown_card', []);

        $this->assertNull($order->getTaxAmount());
        $this->assertEqualsWithDelta(100.0, $order->getGrandTotal(), 0.001);
    }

    public function testSingleCardRejectsTamperedInterest()
    {
        $this->calculator->shouldReceive('calculateInterestInCents')->andReturn(199);

        $this->expectException(LocalizedException::class);
        $this->execute('pagarme_creditcard', [
            'cc_type' => 'visa',
            'cc_installments' => 3,
            'cc_card_tax_amount' => '0',
        ]);
    }

    public function testTwoCardsUsesServerCalculatedInterestSum()
    {
        $this->calculator->shouldReceive('calculateInterestInCents')->with('visa', 2, 6000)->andReturn(100);
        $this->calculator->shouldReceive('calculateInterestInCents')->with('master', 1, 4000)->andReturn(0);

        $order = $this->execute('pagarme_two_creditcard', [
            'cc_first_card_amount' => '60,00',
            'cc_second_card_amount' => '40,00',
            'cc_type_first' => 'visa',
            'cc_type_second' => 'master',
            'cc_installments_first' => 2,
            'cc_installments_second' => 1,
            'cc_first_card_tax_amount' => '1.00',
            'cc_second_card_tax_amount' => '0',
        ]);

        $this->assertEqualsWithDelta(101.0, $order->getGrandTotal(), 0.001);
    }

    public function testTwoCardsRejectsNegativeClientInterest()
    {
        $this->calculator->shouldReceive('calculateInterestInCents')->andReturn(100, 0);

        $this->expectException(LocalizedException::class);
        $this->execute('pagarme_two_creditcard', [
            'cc_first_card_amount' => '60,00',
            'cc_second_card_amount' => '40,00',
            'cc_type_first' => 'visa',
            'cc_type_second' => 'master',
            'cc_installments_first' => 2,
            'cc_installments_second' => 1,
            'cc_first_card_tax_amount' => '-50',
            'cc_second_card_tax_amount' => '0',
        ]);
    }

    public function testTwoCardsRejectsSplitThatDoesNotSumToTotal()
    {
        $this->calculator->shouldNotReceive('calculateInterestInCents');

        $this->expectException(LocalizedException::class);
        $this->execute('pagarme_two_creditcard', [
            'cc_first_card_amount' => '1,00',
            'cc_second_card_amount' => '1,00',
            'cc_type_first' => 'visa',
            'cc_type_second' => 'master',
        ]);
    }

    public function testBilletCardCalculatesInterestOnCardAmount()
    {
        $this->calculator->shouldReceive('calculateInterestInCents')->with('visa', 2, 7000)->andReturn(140);

        $order = $this->execute('pagarme_billet_creditcard', [
            'cc_cc_amount' => '70,00',
            'cc_billet_amount' => '30,00',
            'cc_type' => 'visa',
            'cc_installments' => 2,
            'cc_cc_tax_amount' => '1.40',
        ]);

        $this->assertEqualsWithDelta(101.4, $order->getGrandTotal(), 0.001);
    }

    public function testBilletCardRejectsSplitThatDoesNotSumToTotal()
    {
        $this->expectException(LocalizedException::class);
        $this->execute('pagarme_billet_creditcard', [
            'cc_cc_amount' => '70,00',
            'cc_billet_amount' => '1,00',
            'cc_type' => 'visa',
            'cc_installments' => 1,
        ]);
    }
}
