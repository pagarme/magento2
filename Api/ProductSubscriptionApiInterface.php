<?php

namespace MundiPagg\MundiPagg\Api;

use \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;

interface ProductSubscriptionApiInterface
{
    /**
     * Save product subscription
     *
     * @param ProductSubscriptionInterface $productSubscription
     * @return mixed
     */
    public function save(ProductSubscriptionInterface $productSubscription);
}