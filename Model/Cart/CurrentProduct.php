<?php

namespace MundiPagg\MundiPagg\Model\Cart;

use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;

class CurrentProduct
{
    protected $isNormalProduct = false;
    protected $repetitionSelected;
    protected $productSubscriptionSelected;

    /**
     * @return bool
     */
    public function isNormalProduct()
    {
        return $this->isNormalProduct;
    }

    /**
     * @param bool $isNormalProduct
     */
    public function setIsNormalProduct($isNormalProduct)
    {
        $this->isNormalProduct = $isNormalProduct;
    }

    /**
     * @return RepetitionInterface
     */
    public function getRepetitionSelected()
    {
        return $this->repetitionSelected;
    }

    /**
     * @param RepetitionInterface $repetitionSelected
     */
    public function setRepetitionSelected(RepetitionInterface $repetitionSelected)
    {
        $this->repetitionSelected = $repetitionSelected;
    }

    /**
     * @return ProductSubscriptionInterface
     */
    public function getProductSubscriptionSelected()
    {
        return $this->productSubscriptionSelected;
    }

    /**
     * @param ProductSubscriptionInterface $productSubscriptionSelected
     */
    public function setProductSubscriptionSelected(ProductSubscriptionInterface $productSubscriptionSelected)
    {
        $this->productSubscriptionSelected = $productSubscriptionSelected;
    }


}