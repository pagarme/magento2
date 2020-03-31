<?php


namespace MundiPagg\MundiPagg\Api\ObjectMapper\ProductSubscription;


interface ProductSubscriptionMapperInterface extends \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface
{
    /**
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductSubscription\RepetitionInterface[]|null
     */
    public function getRepetitions();

}
