<?php

namespace Pagarme\Pagarme\Api;

interface InstallmentsByBrandManagementInterface
{
    /**
     * @param mixed $brand
     * @return mixed
     */
    public function getInstallmentsByBrand($brand);

}
