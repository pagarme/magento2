<?php

namespace Pagarme\Pagarme\Model;

use Pagarme\Core\Middle\Client;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class CoreAuth extends Client
{
    private Config $config;
    
    public function _construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function getHubToken()
    {
        return $this->config->getSecretKey();
    }
}
