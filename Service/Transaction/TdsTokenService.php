<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Model\CoreAuth;

class TdsTokenService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    /**

     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->coreAuth = new CoreAuth('');
        $this->config = $config;
    }

    /**
     * @param string $identifier Payment profile ID (pp_*) or account ID (acc_*)
     * @return array{tds_token: string}|array Empty array when no token is returned
     */
    public function getTdsToken($identifier)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        $environment = 'live';
        if ($this->config->isSandboxMode()) {
            $environment = 'test';
        }
        $token = $tdsTokenProxy->getTdsToken($environment, $identifier)->tdsToken;
        if (empty($token)) {
            return [];
        }
        return ['tds_token' => $token];
    }
}
