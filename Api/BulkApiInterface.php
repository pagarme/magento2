<?php

namespace Pagarme\Pagarme\Api;

interface BulkApiInterface
{
    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @return mixed
     */
    public function execute();
}