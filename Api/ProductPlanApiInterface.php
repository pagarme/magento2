<?php

namespace MundiPagg\MundiPagg\Api;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;
use MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi;

interface ProductPlanApiInterface
{
    /**
     * Save product plan
     *
     * @param \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi $productPlan
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi|array
     */
    public function save(\MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi $productPlan, $id = null);

    /**
     * Save product plan
     *
     * @param array $form
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi|array
     */
    public function saveFormData();

    /**
     * List product plan
     *
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi[]|array
     */
    public function list();

    /**
     * Update product plan
     *
     * @param int $id
     * @param \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi $productPlan
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi|array
     */
    public function update($id, \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi $productPlan);

    /**
     * Get a product plan
     *
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi|array
     */
    public function find($id);

    /**
     * Delete product plan
     *
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\ProductPlanInterfaceApi|array
     */
    public function delete($id);

}