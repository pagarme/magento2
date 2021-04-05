<?php

namespace Pagarme\Pagarme\Api;

use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;
use Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface;

interface ProductPlanApiInterface
{
    /**
     * Save product plan
     *
     * @param \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface $productPlan
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function save($productPlan, $id = null);

    /**
     * Save product plan
     *
     * @param array $form
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function saveFormData();

    /**
     * List product plan
     *
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface[]|array
     */
    public function list();

    /**
     * Update product plan
     *
     * @param int $id
     * @param \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface $productPlan
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function update($id, $productPlan);

    /**
     * Get a product plan
     *
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function find($id);

    /**
     * Delete product plan
     *
     * @param int $id
     * @return \Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function delete($id);

}
