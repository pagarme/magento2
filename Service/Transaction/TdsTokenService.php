<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Pagarme\Model\CoreAuth;

class TdsTokenService
{

    /**
     * @var CoreAuth
     */
    private $coreAuth;
    public function __construct(
        CoreAuth $coreAuth
    ) {
        $this->coreAuth = $coreAuth;
    }

    public function getTdsToken($accountId)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        $tds = $tdsTokenProxy->getTdsToken($accountId);
        return $tds->tds_token;
    }
}
