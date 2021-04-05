<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Framework\Exception\LocalizedException;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts;
use Pagarme\Core\Recurrence\Services\CartRules\CurrentProduct;
use Pagarme\Core\Recurrence\Services\CartRules\JustOneProductPlanInCart;
use Pagarme\Core\Recurrence\Services\CartRules\JustProductPlanInCart;
use Pagarme\Core\Recurrence\Services\CartRules\JustSelfProductPlanInCart;
use Pagarme\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Pagarme\Core\Recurrence\Services\CartRules\NormalWithRecurrenceProduct;
use Pagarme\Core\Recurrence\Services\CartRules\ProductListInCart;
use Pagarme\Core\Recurrence\Services\CartRules\RuleInterface;

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
