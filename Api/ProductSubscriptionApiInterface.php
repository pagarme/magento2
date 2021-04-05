<?php

namespace Pagarme\Pagarme\Api;

use Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface;

interface ProductSubscriptionApiInterface
{
    /**
     * Save product subscription
     *
     * @param \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface $productSubscription
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface|array
     */
    public function save($productSubscription, $id = null);

    /**
     * Save product subscription
     *
     * @param array $form
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface|array
     */
    public function saveFormData();

    /**
     * List product subscription
     *
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface[]
     */
    public function list();

    /**
     * Update product subscription
     *
     * @param int $id
     * @param \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface $productSubscription
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface|array
     */
    public function update($id, $productSubscription);

    /**
     * Get a product subscription
     *
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface
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
