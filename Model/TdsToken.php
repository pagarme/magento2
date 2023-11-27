<?php

namespace Pagarme\Pagarme\Model;

use Pagarme\Pagarme\Api\TdsTokenInterface;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Pagarme\Pagarme\Service\Customer\TdsTokenService;

class TdsToken implements TdsTokenInterface
{

    private PagarmeConfigProvider $pagarmeConfig;
    private TdsTokenService $tdsTokenService;
    public function __construct(
        PagarmeConfigProvider $pagarmeConfig,
        TdsTokenService $tdsTokenService
    ) {
        $this->pagarmeConfig = $pagarmeConfig;
        $this->tdsTokenService = $tdsTokenService;
    }
    public function getToken()
    {
        $accountId = $this->pagarmeConfig->getAccountId();
        if (empty($accountId)) {
            return [];
        }
        return $this->tdsTokenService->getTdsToken($accountId);
    }
}
