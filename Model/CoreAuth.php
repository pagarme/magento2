<?php

namespace Pagarme\Pagarme\Model;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Middle\Client;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class CoreAuth extends Client
{
    public function getHubToken()
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->get(\Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config::class);
        return $config->getSecretKey();
    }
}
