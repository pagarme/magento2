<?php

namespace Pagarme\Pagarme\Api;

interface ChargeApiInterface
{
    /**
     * @param string $id
     * @return Pagarme\Pagarme\Model\Api\ResponseMessage
     */
    public function cancel($id);
}