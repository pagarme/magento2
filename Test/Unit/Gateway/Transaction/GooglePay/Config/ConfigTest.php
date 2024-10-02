<?php
namespace Pagarme\Pagarme\Test\Unit\Gateway\Transaction\GooglePay\Config;

use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Gateway\Transaction\GooglePay\Config\Config;

class ConfigTest extends BaseTest
{
    public function testGetCardBrandsIfOnlyOneBrandActive()
    {
        $config = \Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn('visa');
        $allowedBrands = $config->getCardBrands();
        $this->assertIsArray($allowedBrands);
        $this->assertEquals(['VISA'], $allowedBrands);
    }
    public function testGetCardBrandsIfTwoBrandsActives()
    {
        $config = \Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn('visa,mastercard');
        $allowedBrands = $config->getCardBrands();
        $this->assertIsArray($allowedBrands);
        $this->assertEquals(['VISA', 'MASTERCARD'], $allowedBrands);
    }

    public function testGetCardBrandsWithBrandNotAllowedForGooglePay()
    {
        $config = \Mockery::mock(Config::class)->makePartial(['getConfig']);
        $config->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('getConfig')->andReturn('visa,jcb');
        $allowedBrands = $config->getCardBrands();
        $this->assertIsArray($allowedBrands);
        $this->assertEquals(['VISA'], $allowedBrands);
    }
}
