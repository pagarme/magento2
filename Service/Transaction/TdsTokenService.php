<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Pagarme\Model\CoreAuth;

class TdsTokenService
{

    private CoreAuth $coreAuth;
    public function __construct(
        CoreAuth $coreAuth,
    ) {
        $this->coreAuth = $coreAuth;
    }

    public function getTdsToken($accountId)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        return $tdsTokenProxy->getTdsToken($accountId);
    }
}
