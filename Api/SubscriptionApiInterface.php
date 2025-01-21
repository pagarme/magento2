<?php

namespace Pagarme\Pagarme\Api;

interface SubscriptionApiInterface
{
    /**
     * List product subscription
     *
     * @return mixed
     */
    public function list();

    /**
     * Cancel subscription
     *
     * @param int $id
     * @return mixed
     */
    public function cancel($id);

}
