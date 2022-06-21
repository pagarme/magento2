<?php

namespace Pagarme\Pagarme\Plugin\Princing\Render;

use Pagarme\Pagarme\Helper\ProductHelper;

class ProductPriceCyclesPlugin
{

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * CartAddProductAfterObserver constructor.
     * @param ProductHelper $productHelper
     * @throws Exception
     */
    public function __construct(
    \Pagarme\Pagarme\Helper\ProductHelper $productHelper
    ) {
        $this->productHelper = $productHelper;
    }

    public function beforeSetTemplate()
    {
        return ['Pagarme_Pagarme::product/priceCycles.phtml'];
    }

    /**
     * @param string $title
     * @param Product $product
     * @return string
     */
    public static function applyDiscount($title, $product)
    {
        return ProductHelper::applyDiscount($title, $product);
    }
}
