<?php

namespace Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription;

interface ProductSubscriptionMapperInterface extends \Pagarme\Core\Recurrence\Interfaces\ProductSubscriptionInterface
{
    /**
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\RepetitionInterface[]|null
     */
    public function getRepetitions();

}
