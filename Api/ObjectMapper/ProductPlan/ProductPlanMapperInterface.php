<?php

namespace Pagarme\Pagarme\Api\ObjectMapper\ProductPlan;

use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanMapperInterface extends \Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface
{
    /**
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\SubProduct[]
     */
    public function getItems();
}
