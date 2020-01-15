<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;

class ProductPlanHelper
{
    /**
     * @param ProductPlanInterface $planOriginal
     * @param ProductPlanInterface $productPlan
     */
    public static function mapperProductPlanUpdate(
        ProductPlanInterface $planOriginal,
        ProductPlanInterface $productPlan
    ) {
        $planOriginal->setBoleto($productPlan->getBoleto());
        $planOriginal->setCreditCard($productPlan->getCreditCard());
        $planOriginal->setAllowInstallments($productPlan->getAllowInstallments());
        $planOriginal->setIntervalCount($productPlan->getIntervalCount());
        $planOriginal->setIntervalType($productPlan->getIntervalType());
        $planOriginal->setTrialPeriodDays($productPlan->getTrialPeriodDays());
    }

    /**
     * @param ProductPlanInterface $productPlan
     * @throws \Exception
     */
    public static function mapperProductPlan(ProductPlanInterface $productPlan)
    {
        $objectManager = ObjectManager::getInstance();

        /**
         * @var $productBundle Product
         */
        $productBundle = $objectManager
            ->create(Product::class)
            ->load($productPlan->getProductId());

        $productPlan->setName($productBundle->getName());

        foreach ($productPlan->getItems() as $subProduct) {
            /* @var $product Product */
            $product = $objectManager
                ->create(Product::class)
                ->load($subProduct->getProductId());

            $subProduct->setRecurrenceType('plan');
            $subProduct->setName($product->getName());

            $moneyService = new MoneyService();
            $price = $moneyService->floatToCents($product->getPrice());

            $subProduct->setPricingScheme(PricingScheme::UNIT($price));
        }
    }
}
