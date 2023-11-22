<?php

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Config;

interface TdsConfigInterface
{

    public function getTdsActive();
    public function getOrderWithTdsRefused();
}
