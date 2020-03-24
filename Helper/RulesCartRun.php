<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Framework\Exception\LocalizedException;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\JustOneProductPlanInCart;
use Mundipagg\Core\Recurrence\Services\CartRules\JustProductPlanInCart;
use Mundipagg\Core\Recurrence\Services\CartRules\JustSelfProductPlanInCart;
use Mundipagg\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\NormalWithRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\ProductListInCart;
use Mundipagg\Core\Recurrence\Services\CartRules\RuleInterface;

class RulesCartRun
{
    /**
     * @return RuleInterface[]
     */
    private function getRulesProductPlan()
    {
        return [
            new JustProductPlanInCart(),
            new JustSelfProductPlanInCart(),
            new JustOneProductPlanInCart()
        ];
    }

    /**
     * @return RuleInterface[]
     */
    private function getRulesProductSubscription()
    {
        $recurrenceConfiguration = MPSetup::getModuleConfiguration()
            ->getRecurrenceConfig();

        return [
            new NormalWithRecurrenceProduct($recurrenceConfiguration),
            new MoreThanOneRecurrenceProduct($recurrenceConfiguration),
            new CompatibleRecurrenceProducts()
        ];
    }

    public function runRulesProductPlan(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        foreach ($this->getRulesProductPlan() as $rule) {
            $rule->run($currentProduct, $productListInCart);
            if (!empty($rule->getError())) {
                throw new LocalizedException(__($rule->getError()));
            }
        }
    }

    public function runRulesProductSubscription(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        foreach ($this->getRulesProductSubscription() as $rule) {
            $rule->run($currentProduct, $productListInCart);

            if (!empty($rule->getError())) {
                throw new LocalizedException(__($rule->getError()));
            }
        }
    }
}
