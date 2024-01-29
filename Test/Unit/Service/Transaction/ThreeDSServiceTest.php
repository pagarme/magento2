<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Transaction;

use Mockery;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Service\Transaction\ThreeDSService;
use Pagarme\Pagarme\Model\Ui\CreditCard\ConfigProvider as CreditCardConfigProvider;

/**
 * @covers \Pagarme\Pagarme\Service\Transaction\ThreeDSService
 */
class ThreeDSServiceTest extends BaseTest
{

    private $paymentMock;
    private $debitConfigMock;
    private $creditConfigMock;
    private $mpSetupMock;
    private $platformOrderMock;
    protected function setUp() : void
    {
        $this->paymentMock = Mockery::mock('Magento\Sales\Model\Order\Payment');
        $this->creditConfigMock = Mockery::mock('Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config');
        $this->mpSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $this->platformOrderMock = Mockery::mock('Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator');
    }

    public function testIfHasThreeDSAuthorization()
    {
        $tdsService = new ThreeDSService($this->creditConfigMock);
        $paymentMock = $this->paymentMock;
        $paymentMock->shouldReceive('getAdditionalInformation')->andReturn(['authentication' => '{"trans_status":"N", "cc_type":"visa"}']);
        $hasAuth = $tdsService->hasThreeDSAuthorization($paymentMock);
        $this->assertNotFalse($hasAuth);
    }

    public function testIfNotHaveThreeDSAuthorization()
    {
        $tdsService = new ThreeDSService($this->creditConfigMock);
        $paymentMock = $this->paymentMock;
        $paymentMock->shouldReceive('getAdditionalInformation')->andReturn(true);   
        $hasAuth = $tdsService->hasThreeDSAuthorization($paymentMock);
        $this->assertFalse($hasAuth);
    }
    

    public function testProcessDeclinedThreeDsTransaction()
    {   
        $this->expectException('Magento\Framework\Exception\PaymentException');

        $platformOrderMock = $this->platformOrderMock;
        $platformOrderMock->shouldReceive('getCode')->andReturn('007');
        $platformOrderMock->shouldReceive('setStatus')->once()->andReturnSelf();
        $platformOrderMock->shouldReceive('setState')->once()->andReturnSelf();
        $platformOrderMock->shouldReceive('save')->once()->andReturnSelf();
        
        $mpSetupMock = $this->mpSetupMock;
        $mpSetupMock->shouldReceive('getLogPath')->andReturn("./temp/");
        $mpSetupMock->shouldReceive('getModuleConfiguration')->andReturnSelf();
        $mpSetupMock->shouldReceive('isCreateOrderEnabled')->andReturn(true);
        
        $paymentMock = $this->paymentMock;
        $paymentMock->shouldReceive('getStatus')->andReturn('N');
        $paymentMock->shouldReceive('getMethod')->andReturn(CreditCardConfigProvider::CODE);
        $paymentMock->shouldReceive('getAdditionalInformation')->andReturn(['authentication' => '{"trans_status":"N", "cc_type":"visa"}']);
        
        $this->creditConfigMock->shouldReceive('getOrderWithTdsRefused')->andReturn(false);

        $tdsService = new ThreeDSService($this->creditConfigMock);
        $tdsService->processDeclinedThreeDsTransaction($paymentMock, $platformOrderMock);  
    }

    public function testProcessDeclinedThreeDsTransactionButIsNotTdsPayment()
    {
        $platformOrderMock = $this->platformOrderMock;
        
        $paymentMock = $this->paymentMock;
        $paymentMock->shouldReceive('getMethod')->andReturn(CreditCardConfigProvider::CODE);
        $paymentMock->shouldReceive('getAdditionalInformation')->andReturn(['authentication' => ""]);

        $this->creditConfigMock->shouldReceive('getOrderWithTdsRefused')->andReturn(false);
        
        $tdsService = new ThreeDSService($this->creditConfigMock);
        $emptyReturn = $tdsService->processDeclinedThreeDsTransaction($paymentMock, $platformOrderMock);  
        $this->assertEmpty($emptyReturn);
    }

    public function testProcessDeclinedThreeDsTransactionWithRefusedDisabled()
    {   
        $this->expectException('Magento\Framework\Exception\PaymentException');
        
        $mpSetupMock = $this->mpSetupMock;
        $mpSetupMock->shouldReceive('getModuleConfiguration')->andReturnSelf();
        $mpSetupMock->shouldReceive('isCreateOrderEnabled')->andReturn(false);
        
        $platformOrderMock = Mockery::mock('\Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator');
        
        $paymentMock = $this->paymentMock;
        $paymentMock->shouldReceive('getStatus')->andReturn('Y');
        $paymentMock->shouldReceive('getMethod')->andReturn(CreditCardConfigProvider::CODE);
        $paymentMock->shouldReceive('getAdditionalInformation')->andReturn(['authentication' => '{"trans_status":"Y", "cc_type":"visa"}']);

        $this->creditConfigMock->shouldReceive('getOrderWithTdsRefused')->andReturn(false);
        
        $tdsService = new ThreeDSService($this->creditConfigMock);
        $tdsService->processDeclinedThreeDsTransaction($paymentMock, $platformOrderMock);
    }
}
