<?php

namespace MundiPagg\MundiPagg\Api;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanInterfaceApi extends \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface
{
    /**
     * @return \MundiPagg\MundiPagg\Api\SubProduct[]
     */
    public function getItems();
}
