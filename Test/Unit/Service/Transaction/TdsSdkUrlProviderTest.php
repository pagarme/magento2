<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Transaction;

use Mockery;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Service\Transaction\TdsSdkUrlProvider;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * @covers \Pagarme\Pagarme\Service\Transaction\TdsSdkUrlProvider
 */
class TdsSdkUrlProviderTest extends BaseTest
{
    private function urlFor($isSandbox)
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('isSandboxMode')->andReturn($isSandbox);

        return (new TdsSdkUrlProvider($config))->getUrl();
    }

    public function testSandboxStoresUseTheTestSdk()
    {
        $this->assertSame('https://3ds-nx-js.stone.com.br/test/v2/3ds2.min.js', $this->urlFor(true));
    }

    public function testProductionStoresUseTheLiveSdk()
    {
        $this->assertSame('https://3ds-nx-js.stone.com.br/live/v2/3ds2.min.js', $this->urlFor(false));
    }
}
