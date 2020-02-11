<?php

namespace MundiPagg\MundiPagg\Model\Cart;

use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;

class ProductListInCart
{
    /** @var ProductSubscriptionInterface */
    private $recurrenceProduct;

    /** @var RepetitionInterface */
    private $repetition;

    /** @var array */
    private $normalProducts = [];

    /** @var array ProductSubscriptionInterface */
    private $recurrenceProducts = [];

    /**
     * @return ProductSubscriptionInterface
     */
    public function getRecurrenceProduct()
    {
        return $this->recurrenceProduct;
    }

    /**
     * @param ProductSubscriptionInterface $recurrenceProduct
     */
    public function setRecurrenceProduct(ProductSubscriptionInterface $recurrenceProduct)
    {
        $this->recurrenceProduct = $recurrenceProduct;
    }

    /**
     * @return ProductSubscriptionInterface[]
     */
    public function getRecurrenceProducts()
    {
        return $this->recurrenceProducts;
    }

    /**
     * @param ProductSubscriptionInterface $recurrenceProduct
     */
    public function addRecurrenceProduct(ProductSubscriptionInterface $recurrenceProduct)
    {
        $this->recurrenceProducts[] = $recurrenceProduct;
    }

    /**
     * @return array
     */
    public function getNormalProducts()
    {
        return $this->normalProducts;
    }

    /**
     * @param array $normalProduct
     */
    public function addNormalProducts($normalProduct)
    {
        $this->normalProducts[] = $normalProduct;
    }

    /**
     * @return RepetitionInterface
     */
    public function getRepetition()
    {
        return $this->repetition;
    }

    /**
     * @param RepetitionInterface $repetition
     */
    public function setRepetition(RepetitionInterface $repetition)
    {
        $this->repetition = $repetition;
    }
}