<?php

namespace Pagarme\Pagarme\Api\ObjectMapper\ProductPlan;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanMapperInterface extends \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface
{
    /**
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\SubProduct[]
     */
    public function getItems();
}
