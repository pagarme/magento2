<?php

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Command;

use Pagarme\Pagarme\Gateway\Transaction\Base\Command\AbstractApiCommand;

class AuthorizeCommand extends AbstractApiCommand
{
    /**
     * @param $request
     * @return mixed
     */
    protected function sendRequest($request)
    {
        if (!isset($request)) {
            throw new \InvalidArgumentException('Pagar.me Request object should be provided');
        }
        return $request;
    }
}

