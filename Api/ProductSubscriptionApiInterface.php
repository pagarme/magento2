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

    /**
     * List product subscription
     *
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface[]
     */
    public function list();

    /**
     * Update product subscription
     *
     * @param int $id
     * @param ProductSubscriptionInterface $productSubscription
     * @return mixed
     */
    public function update($id, ProductSubscriptionInterface $productSubscription);

    /**
     * Get a product subscription
     *
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface
     */
    public function getProductSubscription($id);

    /**
     * Delete product subscription
     *
     * @param int $id
     * @return mixed
     */
    public function delete($id);

}