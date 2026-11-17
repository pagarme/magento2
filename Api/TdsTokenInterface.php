<?php

namespace Pagarme\Pagarme\Api;

interface TdsTokenInterface
{
    /**
     * @return array{tds_token: string}|array
     */
    public function getToken();
}
