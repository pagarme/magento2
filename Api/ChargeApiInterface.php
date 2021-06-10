<?php

namespace Pagarme\Pagarme\Api;

interface ChargeApiInterface
{
    /**
     * @param $id
     * @return Pagarme\Pagarme\Model\Api\ResponseMessage
     */
    public function cancel($id);
}