<?php

namespace Pagarme\Pagarme\Api;

interface TdsTokenInterface
{
    /**
     * @return array{provider: string, tds_token: string}|array
     */
    public function getToken();
}
