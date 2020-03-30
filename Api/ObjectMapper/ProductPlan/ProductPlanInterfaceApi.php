<?php

namespace MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanInterfaceApi extends \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface
{
    /**
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\SubProduct[]
     */
    public function getItems();
}
