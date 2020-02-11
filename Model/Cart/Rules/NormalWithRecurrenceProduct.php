<?php

namespace MundiPagg\MundiPagg\Model\Cart\Rules;

use Magento\Framework\Exception\LocalizedException;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use MundiPagg\MundiPagg\Model\Cart\CurrentProduct;
use MundiPagg\MundiPagg\Model\Cart\ProductListInCart;

class NormalWithRecurrenceProduct implements RuleInterface
{
    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddNormalProductWithRecurrenceProduct =
            MPSetup::getModuleConfiguration()
                ->getRecurrenceConfig()
                ->isPurchaseRecurrenceProductWithNormalProduct();

        $messageConflictRecurrence =
            MPSetup::getModuleConfiguration()
                ->getRecurrenceConfig()
                ->getConflictMessageRecurrenceProductWithNormalProduct();

        if (
            !$canAddNormalProductWithRecurrenceProduct  &&
            ($currentProduct->isNormalProduct() && !empty($productListInCart->getRecurrenceProducts()))
        ) {
            throw new LocalizedException(__($messageConflictRecurrence));
        }

        if (
            !$canAddNormalProductWithRecurrenceProduct  &&
            (!$currentProduct->isNormalProduct() && !empty($productListInCart->getNormalProducts()))
        ) {
            throw new LocalizedException(__($messageConflictRecurrence));
        }

        return;
    }
}