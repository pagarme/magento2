<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class TdsSdkUrlProvider
{
    const BASE_URL = 'https://3ds-nx-js.stone.com.br';

    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $environment = $this->config->isSandboxMode() ? 'test' : 'live';

        return self::BASE_URL . '/' . $environment . '/v2/3ds2.min.js';
    }
}
