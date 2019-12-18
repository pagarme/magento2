<?php

namespace MundiPagg\MundiPagg\Api;

interface SubscriptionApiInterface
{
    /**
     * List product subscription
     *
     * @return mixed
     */
    public function list();

    /**
     * List product subscription
     *
     * @param string $customerId
     * @return \Mundipagg\Core\Recurrence\Interfaces\SubscriptionInterface[]
     */
    public function listByCustomerId($customerId);

    /**
     * Cancel subscription
     *
     * @param int $id
     * @return mixed
     */
    public function cancel($id);

}