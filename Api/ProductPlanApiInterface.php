<?php

namespace MundiPagg\MundiPagg\Api;

use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;

interface ProductPlanApiInterface
{
    /**
     * Save product subscription
     *
     * @param ProductPlanInterface $productPlan
     * @param int $id
     * @return ProductPlanInterface|array
     */
    public function save(ProductPlanInterface $productPlan, $id = null);

    /**
     * Save product subscription
     *
     * @param array $form
     * @param int $id
     * @return ProductPlanInterface|array
     */
    public function saveFormData();

    /**
     * List product subscription
     *
     * @return ProductPlanInterface[]
     */
    public function list();

    /**
     * Update product subscription
     *
     * @param int $id
     * @param ProductPlanInterface $productSubscription
     * @return ProductPlanInterface|array
     */
    public function update($id, ProductPlanInterface $productSubscription);

    /**
     * Get a product subscription
     *
     * @param int $id
     * @return ProductPlanInterface
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