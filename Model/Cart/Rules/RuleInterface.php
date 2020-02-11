<?php


namespace MundiPagg\MundiPagg\Model\Cart\Rules;


use MundiPagg\MundiPagg\Model\Cart\CurrentProduct;
use MundiPagg\MundiPagg\Model\Cart\ProductListInCart;

interface RuleInterface
{
    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    );
}