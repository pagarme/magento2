<?php

namespace MundiPagg\MundiPagg\Api;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanApiInterface
{
    /**
     * Save product plan
     *
     * @param ProductPlanInterface $productPlan
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface|array
     */
    public function save(ProductPlanInterface $productPlan, $id = null);

    /**
     * Save product plan
     *
     * @param array $form
     * @param int $id
     * @return ProductPlanInterface|array
     */
    public function saveFormData();

    /**
     * List product plan
     *
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface[]|array
     */
    public function list();

    /**
     * Update product plan
     *
     * @param int $id
     * @param ProductPlanInterface $productPlan
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface|array
     */
    public function update($id, ProductPlanInterface $productPlan);

    /**
     * Get a product plan
     *
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface|array
     */
    public function find($id);

    /**
     * Delete product plan
     *
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface|array
     */
    public function delete($id);

}