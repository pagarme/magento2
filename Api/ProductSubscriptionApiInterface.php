<?php

namespace MundiPagg\MundiPagg\Api;

use \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi;

interface ProductSubscriptionApiInterface
{
    /**
     * Save product subscription
     *
     * @param \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi $productSubscription
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi|array
     */
    public function save(\MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi $productSubscription, $id = null);

    /**
     * Save product subscription
     *
     * @param array $form
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi|array
     */
    public function saveFormData();

    /**
     * List product subscription
     *
     * @return \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi[]
     */
    public function list();

    /**
     * Update product subscription
     *
     * @param int $id
     * @param \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi $productSubscription
     * @return \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi|array
     */
    public function update($id, \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi $productSubscription);

    /**
     * Get a product subscription
     *
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductSubscriptionInterfaceApi
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