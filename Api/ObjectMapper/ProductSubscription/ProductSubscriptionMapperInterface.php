<?php

namespace Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription;

interface ProductSubscriptionMapperInterface extends \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface
{
    /**
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\RepetitionInterface[]|null
     */
    public function getRepetitions();

}
