<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use Pagarme\Pagarme\Service\Payment\ClientPaymentAmountValidator;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Service\Payment\ClientPaymentAmountValidator
 */
class ClientPaymentAmountValidatorTest extends BaseTest
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new ClientPaymentAmountValidator();
    }

    public function testParsesMaskedAmountWithThousandsSeparator()
    {
        $this->assertSame(123456, $this->validator->parseMaskedAmountToCents('1.234,56'));
    }

    public function testRejectsEmptyMaskedAmount()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->parseMaskedAmountToCents('');
    }

    public function testRejectsNegativeMaskedAmount()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->parseMaskedAmountToCents('-10,00');
    }

    public function testAcceptsInterestWithinOneCentTolerance()
    {
        $this->validator->assertInterestMatches(1234, '12.35');
        $this->addToAssertionCount(1);
    }

    public function testRejectsInterestBeyondTolerance()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertInterestMatches(1234, '12.00');
    }

    public function testRejectsNegativeInterestWhenInterestIsExpected()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertInterestMatches(1234, '-5');
    }

    public function testMissingInterestIsZero()
    {
        $this->validator->assertInterestMatches(0, null);
        $this->addToAssertionCount(1);
    }

    public function testMissingInterestIsRejectedWhenInterestIsExpected()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertInterestMatches(1234, null);
    }

    public function testRejectsNonNumericInterest()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertInterestMatches(0, 'abc');
    }

    public function testAcceptsSplitThatSumsToTotal()
    {
        $this->validator->assertSplitMatchesTotal(10000, [6000, 4000]);
        $this->addToAssertionCount(1);
    }

    public function testRejectsSplitThatDoesNotSumToTotal()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertSplitMatchesTotal(100000, [100, 100]);
    }

    public function testRejectsSplitWithNonPositivePart()
    {
        $this->expectException(LocalizedException::class);
        $this->validator->assertSplitMatchesTotal(10000, [10000, 0]);
    }
}
