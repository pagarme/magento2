<?php

namespace MundiPagg\MundiPagg\Model\Cart\Rules;

use Magento\Framework\Exception\LocalizedException;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Services\RulesCheckoutService;
use MundiPagg\MundiPagg\Model\Cart\CurrentProduct;
use MundiPagg\MundiPagg\Model\Cart\ProductListInCart;

class CompatibleRecurrenceProducts implements RuleInterface
{
    /**
     * @var RulesCheckoutService
     */
    private $rulesCheckoutService;

    /**
     * @var LocalizationService
     */
    private $i18n;

    public function __construct()
    {
        $this->rulesCheckoutService = new RulesCheckoutService();
        $this->i18n = new LocalizationService();
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {

        if (
            !$currentProduct->getProductSubscriptionSelected() ||
            !$productListInCart->getRecurrenceProduct()
        ) {
            return;
        }

        $messageConflictRecurrence = $this->i18n->getDashboard(
            "The recurrence products should have the same payment configuration and the same interval"
        );

        $productAreCompatible = $this->rulesCheckoutService->runRulesCheckoutSubscription(
            $productListInCart->getRecurrenceProduct(),
            $currentProduct->getProductSubscriptionSelected(),
            $productListInCart->getRepetition(),
            $currentProduct->getRepetitionSelected()
        );

        if (!$productAreCompatible) {
            throw new LocalizedException(__($messageConflictRecurrence));
        }

        return;
    }
}