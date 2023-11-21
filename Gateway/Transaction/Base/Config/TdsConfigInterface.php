<?php

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Config;

interface TdsConfigInterface
{

    // const TDS = static::TDS;
    public function getTdsActive();
    public function getOrderWithTdsRefused();
}
