<?php

namespace Pagarme\Pagarme\Model;

use Pagarme\Pagarme\Api\TdsTokenInterface;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Pagarme\Pagarme\Service\Transaction\TdsTokenService;

class TdsToken implements TdsTokenInterface
{

    /**
     * @var PagarmeConfigProvider
     */
    private $pagarmeConfig;

    /**
     * @var TdsTokenService
     */
    private $tdsTokenService;

    public function __construct(
        PagarmeConfigProvider $pagarmeConfig,
        TdsTokenService $tdsTokenService
    ) {
        $this->pagarmeConfig = $pagarmeConfig;
        $this->tdsTokenService = $tdsTokenService;
    }
    public function getToken()
    {
        $identifier = $this->pagarmeConfig->getIdentifier();
        if (empty($identifier)) {
            return [];
        }
        return $this->tdsTokenService->getTdsToken($identifier);
    }
}
