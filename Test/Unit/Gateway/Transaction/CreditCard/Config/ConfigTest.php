<?php

namespace Pagarme\Pagarme\Test\Unit\Gateway\Transaction\CreditCard\Config;

use Mockery;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config;
use Pagarme\Pagarme\Model\Enum\TdsModeEnum;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config
 */
class ConfigTest extends BaseTest
{
    public function testGetTdsModeReturnsConfiguredValue()
    {
        $config = Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn(TdsModeEnum::MANDATORY);

        $this->assertEquals(TdsModeEnum::MANDATORY, $config->getTdsMode());
    }

    public function testGetTdsModeDefaultsToOptionalWhenUnset()
    {
        $config = Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn(null);

        $this->assertEquals(TdsModeEnum::OPTIONAL, $config->getTdsMode());
    }

    public function testGetTdsModeDefaultsToOptionalWhenEmpty()
    {
        $config = Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn('');

        $this->assertEquals(TdsModeEnum::OPTIONAL, $config->getTdsMode());
    }
}
