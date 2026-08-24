<?php

namespace Pagarme\Pagarme\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use Mockery;
use Pagarme\Pagarme\Model\Enum\TdsModeEnum;
use Pagarme\Pagarme\Model\Enum\TdsReasonEnum;
use Pagarme\Pagarme\Observer\CreditCardDataAssignObserver;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Observer\CreditCardDataAssignObserver
 */
class CreditCardDataAssignObserverTest extends BaseTest
{
    private $cardsRepositoryMock;
    private $creditCardConfigMock;

    protected function setUp(): void
    {
        $this->cardsRepositoryMock = Mockery::mock('Pagarme\Pagarme\Model\CardsRepository');
        $this->creditCardConfigMock = Mockery::mock('Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config');
    }

    private function makeObserver()
    {
        return new CreditCardDataAssignObserver(
            $this->cardsRepositoryMock,
            $this->creditCardConfigMock
        );
    }

    private function makeInfoMock()
    {
        $mock = Mockery::mock('Magento\Payment\Model\Info');
        $mock->shouldReceive('setAdditionalInformation')->withAnyArgs()->andReturnNull();
        $mock->shouldReceive('addData')->withAnyArgs()->andReturnNull();
        return $mock;
    }

    public function testMandatoryModeRejectsNotEligibleCard()
    {
        $this->expectException(PaymentException::class);

        $this->creditCardConfigMock->shouldReceive('getTdsMode')->andReturn(TdsModeEnum::MANDATORY);

        $additionalData = new DataObject([
            'authentication' => null,
            'tds_reason' => TdsReasonEnum::NOT_ELIGIBLE,
        ]);

        $this->makeObserver()->fillCardData($additionalData, $this->makeInfoMock());
    }

    public function testOptionalModeAllowsNotEligibleCard()
    {
        $this->creditCardConfigMock->shouldReceive('getTdsMode')->andReturn(TdsModeEnum::OPTIONAL);

        $captured = [];
        $info = Mockery::mock('Magento\Payment\Model\Info');
        $info->shouldReceive('setAdditionalInformation')
            ->withAnyArgs()
            ->andReturnUsing(function ($key, $value) use (&$captured) {
                $captured[$key] = $value;
            });
        $info->shouldReceive('addData')->withAnyArgs()->andReturnNull();

        $additionalData = new DataObject([
            'authentication' => null,
            'tds_reason' => TdsReasonEnum::NOT_ELIGIBLE,
        ]);

        $this->makeObserver()->fillCardData($additionalData, $info);

        $this->assertEquals(TdsReasonEnum::NOT_ELIGIBLE, $captured['3ds_reason']);
    }

    public function testMandatoryModeAllowsConfigDisabledSkip()
    {
        $this->creditCardConfigMock->shouldReceive('getTdsMode')->andReturn(TdsModeEnum::MANDATORY);

        $additionalData = new DataObject([
            'authentication' => null,
            'tds_reason' => TdsReasonEnum::CONFIG_DISABLED,
        ]);

        $this->makeObserver()->fillCardData($additionalData, $this->makeInfoMock());
        $this->assertTrue(true);
    }

    public function testDeterminesAuthorizedReasonFromTransStatus()
    {
        $this->creditCardConfigMock->shouldReceive('getTdsMode')->andReturn(TdsModeEnum::OPTIONAL);

        $captured = [];
        $info = Mockery::mock('Magento\Payment\Model\Info');
        $info->shouldReceive('setAdditionalInformation')
            ->withAnyArgs()
            ->andReturnUsing(function ($key, $value) use (&$captured) {
                $captured[$key] = $value;
            });
        $info->shouldReceive('addData')->withAnyArgs()->andReturnNull();

        $additionalData = new DataObject([
            'authentication' => json_encode(['trans_status' => 'Y', 'tds_server_trans_id' => 'abc']),
            'tds_reason' => null,
        ]);

        $this->makeObserver()->fillCardData($additionalData, $info);

        $this->assertEquals(TdsReasonEnum::AUTHORIZED, $captured['3ds_reason']);
    }

    public function testDeterminesDeclinedReasonFromTransStatus()
    {
        $this->creditCardConfigMock->shouldReceive('getTdsMode')->andReturn(TdsModeEnum::OPTIONAL);

        $captured = [];
        $info = Mockery::mock('Magento\Payment\Model\Info');
        $info->shouldReceive('setAdditionalInformation')
            ->withAnyArgs()
            ->andReturnUsing(function ($key, $value) use (&$captured) {
                $captured[$key] = $value;
            });
        $info->shouldReceive('addData')->withAnyArgs()->andReturnNull();

        $additionalData = new DataObject([
            'authentication' => json_encode(['trans_status' => 'N', 'tds_server_trans_id' => 'xyz']),
            'tds_reason' => null,
        ]);

        $this->makeObserver()->fillCardData($additionalData, $info);

        $this->assertEquals(TdsReasonEnum::DECLINED, $captured['3ds_reason']);
    }
}
