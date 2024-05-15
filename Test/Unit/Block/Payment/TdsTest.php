<?php

namespace Pagarme\Pagarme\Test\Unit\Block\Payment;

use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Block\Payment\Tds;
use Mockery;

class TdsTest extends BaseTest
{
    
    protected $contextMock;
    protected $configMock;
    protected $creditCardConfigMock;
    protected $pagarmeConfigMock;
    protected function setUp(): void
    {
        Mockery::close();
        $this->contextMock = Mockery::mock("Context");
        $this->configMock = Mockery::mock("Config");
        $this->pagarmeConfigMock = Mockery::mock("PagarmeConfig");
        $this->creditCardConfigMock = Mockery::mock('Config');
    }
    public function testCanInitTdsWithEmptyValueInConfiguration()
    {
        $this->creditCardConfigMock->shouldReceive('getTdsActive')->andReturn("");
        $tdsClass = new \Reflection(Tds::class);
        $tdsClass->
        $teste = $tdsClass->canInitTds();
        $this->assertEmpty($teste);
    }

    public function testGetSdkUrl()
    {
        $this->pagarmeConfigMock->shouldReceive('isSandboxMode')->andReturn(true);
        $tdsClass = Mockery::instanceMock(Tds::class)->makePartial();
        $teste = $tdsClass->getSdkUrl();
        // var_dump($teste);
        $this->assertNotEmpty($teste);
    }
}
