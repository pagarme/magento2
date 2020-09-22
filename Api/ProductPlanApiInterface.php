<?php

namespace MundiPagg\MundiPagg\Api;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;
use MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface;

interface ProductPlanApiInterface
{
    /**
     * Save product plan
     *
     * @param \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface $productPlan
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function save($productPlan, $id = null);

    /**
     * Save product plan
     *
     * @param array $form
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function saveFormData();

    /**
     * List product plan
     *
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface[]|array
     */
    public function list();

    /**
     * Update product plan
     *
     * @param int $id
     * @param \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface $productPlan
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function update($id, $productPlan);

    /**
     * Get a product plan
     *
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function find($id);

    /**
     * Delete product plan
     *
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface|array
     */
    public function delete($id);

}