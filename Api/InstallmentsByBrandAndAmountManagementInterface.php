<?php

namespace Pagarme\Pagarme\Api;

interface InstallmentsByBrandAndAmountManagementInterface
{
    /**
     * @param mixed $brand
     * @param mixed $amount
     * @return mixed
     */
    public function getInstallmentsByBrandAndAmount($brand, $amount);

}
