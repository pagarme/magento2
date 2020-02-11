<?php

namespace MundiPagg\MundiPagg\Model\Cart\Rules;

use Magento\Framework\Exception\LocalizedException;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use MundiPagg\MundiPagg\Model\Cart\CurrentProduct;
use MundiPagg\MundiPagg\Model\Cart\ProductListInCart;

class MoreThanOneRecurrenceProduct implements RuleInterface
{

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddRecurrenceProductWithRecurrenceProduct =
            MPSetup::getModuleConfiguration()
                ->getRecurrenceConfig()
                ->isPurchaseRecurrenceProductWithRecurrenceProduct();

        $messageConflictRecurrence =
            MPSetup::getModuleConfiguration()
                ->getRecurrenceConfig()
                ->getConflictMessageRecurrenceProductWithRecurrenceProduct();

        if (
            !$canAddRecurrenceProductWithRecurrenceProduct  &&
            (!$currentProduct->isNormalProduct() && !empty($productListInCart->getRecurrenceProducts()))
        ) {
            throw new LocalizedException(__($messageConflictRecurrence));
        }

        return;
    }
}