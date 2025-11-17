<?php

namespace Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription;

use Pagarme\Core\Recurrence\Interfaces\ProductSubscriptionInterface;

interface ProductSubscriptionMapperInterface extends ProductSubscriptionInterface
{
    /**
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\RepetitionInterface[]|null
     */
    public function getRepetitions();

}
