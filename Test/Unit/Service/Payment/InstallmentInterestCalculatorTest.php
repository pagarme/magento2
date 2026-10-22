<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use Mockery;
use Pagarme\Core\Kernel\ValueObjects\Installment;
use Pagarme\Pagarme\Service\Payment\InstallmentInterestCalculator;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Service\Payment\InstallmentInterestCalculator
 */
class InstallmentInterestCalculatorTest extends BaseTest
{
    private function makeCalculator()
    {
        $calculator = Mockery::mock(InstallmentInterestCalculator::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $calculator->shouldReceive('getInstallments')->andReturn([
            new Installment(1, 10000, 0),
            new Installment(2, 10000, 0),
            new Installment(3, 10000, 0.0199),
        ]);

        return $calculator;
    }

    public function testInstallmentWithoutInterestHasZeroInterest()
    {
        $this->assertSame(0, $this->makeCalculator()->calculateInterestInCents('visa', 2, 10000));
    }

    public function testInstallmentWithInterestReturnsInterestInCents()
    {
        $this->assertSame(199, $this->makeCalculator()->calculateInterestInCents('visa', 3, 10000));
    }

    public function testInvalidInstallmentNumberIsRejected()
    {
        $this->expectException(LocalizedException::class);
        $this->makeCalculator()->calculateInterestInCents('visa', 12, 10000);
    }

    public function testInvalidBrandIsRejected()
    {
        $this->expectException(LocalizedException::class);
        $this->makeCalculator()->calculateInterestInCents('not-a-brand', 1, 10000);
    }

    public function testEmptyBrandIsRejected()
    {
        $this->expectException(LocalizedException::class);
        $this->makeCalculator()->calculateInterestInCents(null, 1, 10000);
    }
}
