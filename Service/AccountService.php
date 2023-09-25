<?php

namespace Pagarme\Pagarme\Service;

use Pagarme\Core\Middle\Model\Account;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use Pagarme\Pagarme\Model\CoreAuth;

class AccountService
{
    protected $coreAuth;

    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    public function getAccount($accountId)
    {
        $account = new Account();
        return $this->getAccountOnPagarme($accountId);
    }

    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }
}
