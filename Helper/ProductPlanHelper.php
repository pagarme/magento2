<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;
use Pagarme\Core\Recurrence\Services\SubProductService;
use Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;

class ProductPlanHelper
{
    /**
     * @param ProductPlanInterface $planOriginal
     * @param ProductPlanInterface $productPlan
     * @throws \Exception
     */
    public static function mapperProductPlanUpdate(
        ProductPlanInterface $planOriginal,
        ProductPlanInterface $productPlan
    ) {
        $productPlan->setId($planOriginal->getId());
        $productPlan->setPagarmeId($planOriginal->getPagarmeId());
        $productPlan->setProductId($planOriginal->getProductId());
        $productPlan->setBillingType($planOriginal->getBillingType());
        $productPlan->setStatus('ACTIVE');

        self::mapperProductPlan($productPlan);
    }

    /**
     * @param ProductPlanInterface $productPlan
     * @throws \Exception
     */
    public static function mapperProductPlan(ProductPlanInterface $productPlan) {
        $objectManager = ObjectManager::getInstance();

        /**
         * @var $productBundle Product
         */
        $productBundle = $objectManager
            ->create(Product::class)
            ->load($productPlan->getProductId());

        $productPlan->setName($productBundle->getName());

        $selectedProducts = self::getSelectedProductBundle(
            $productBundle,
            $objectManager
        );

        foreach ($productPlan->getItems() as $subProduct) {
            /* @var $product Product */
            $product = $objectManager
                ->create(Product::class)
                ->load($subProduct->getProductId());

            $subProduct->setRecurrenceType('plan');
            $subProduct->setName($product->getName());

            if (!isset($selectedProducts[$subProduct->getProductId()])) {
                throw new \Exception(
                    "Product id: {$subProduct->getProductId()}. It's not correct"
                    , 404
                );
            }

            $price = $selectedProducts[$subProduct->getProductId()]['price'];
            $quantity = $selectedProducts[$subProduct->getProductId()]['quantity'];

            $subProduct->setQuantity($quantity);
            $subProduct->setPricingScheme(PricingScheme::UNIT($price));

            $subProductSaved = self::getSubProductFromDb(
                $productPlan->getId(),
                $subProduct->getProductId()
            );

            if (!empty($subProductSaved)) {
                $subProduct->setPagarmeId($subProductSaved->getPagarmeId());
                $subProduct->setId($subProductSaved->getId());
            }
        }
    }

    /**
     * @param $planId
     * @param $productId
     * @return \Pagarme\Core\Kernel\ValueObjects\AbstractValidString|null
     */
    protected static function getSubProductFromDb($planId, $productId)
    {
        if (empty($planId)) {
            return null;
        }

        $subProductService = new SubProductService();
        $subProduct = $subProductService->findByRecurrenceIdAndProductId(
            $planId,
            $productId
        );

        if (!$subProduct) {
            return null;
        }

        return $subProduct;
    }

    /**
     * @param $productBundle
     * @param $objectManager
     * @return array
     */
    public static function getSelectedProductBundle($productBundle, $objectManager)
    {
        $typeInstance = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $selectedProducts = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($productBundle),
            $productBundle
        );

        $products = [];
        $moneyService = new MoneyService();

        foreach ($selectedProducts as $product) {

            $products[$product->getEntityId()] = [
                "code" => $product->getEntityId(),
                "name" => $product->getName(),
                "price" => $moneyService->floatToCents(
                    $product->getSelectionPriceValue()
                ),
                "quantity" => (int)$product->getSelectionQty()
            ];
        }

        return $products;
    }
}
