<?php

namespace MundiPagg\MundiPagg\Model\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use Mundipagg\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\NormalWithRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\ProductListInCart;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Magento\Catalog\Model\Product\Interceptor;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\Services\PlanService;
use Mundipagg\Core\Recurrence\Services\CartRules\JustProductPlanInCart;
use Mundipagg\Core\Recurrence\Services\CartRules\JustSelfProductPlanInCart;
use MundiPagg\MundiPagg\Helper\RulesCartRun;

class CartConflict
{
    /**
     * @var RepetitionService
     */
    private $repetitionService;

    /**
     * @var RecurrenceService
     */
    private $recurrenceService;

    /**
     * @var RecurrenceProductHelper
     */
    private $recurrenceProductHelper;

    /**
     * @var ProductSubscriptionService
     */
    private $productSubscriptionService;

    /**
     * @var PlanService
     */
    private $planService;

    /**
     * CartConflict constructor.
     */
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->repetitionService = new RepetitionService();
        $this->recurrenceService = new RecurrenceService();
        $this->recurrenceProductHelper = new RecurrenceProductHelper();
        $this->productSubscriptionService = new ProductSubscriptionService();
        $this->planService = new PlanService();
        $this->rulesCartRun = new RulesCartRun();
    }

    public function beforeUpdateItems(Cart $cart, $dataQty)
    {
        $items = $cart->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            if (!isset($dataQty[$item->getItemId()]['qty'])) {
                continue;
            }

            $productPlan = $this->planService->findByProductId(
                $item->getProduct()->getId()
            );

            if (($productPlan !== null) && ($dataQty[$item->getItemId()]['qty'] > 1)) {
                $i18n = new LocalizationService();
                $message = $i18n->getDashboard('Must be has one product plan on cart');
                throw new LocalizedException(__($message));
            }
        }
    }

    public function beforeAddProduct(
        Cart $cart,
        Interceptor $productInfo,
        $requestInfo = null
    ) {
        $currentProduct = $this->buildCurrentProduct(
            $productInfo,
            $requestInfo
        );

        $productListInCart = $this->buildProductListInCart($cart);

        if (
            $currentProduct->getProductPlanSelected() !== null ||
            $currentProduct->isNormalProduct()
        ) {
            $this->rulesCartRun->runRulesProductPlan($currentProduct, $productListInCart);
        }

        if (
            $currentProduct->getProductSubscriptionSelected() !== null ||
            $currentProduct->isNormalProduct()
        ) {
            $this->rulesCartRun->runRulesProductSubscription(
                $currentProduct,
                $productListInCart
            );
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param Interceptor $productInfo
     * @param null $requestInfo
     * @return CurrentProduct
     */
    protected function buildCurrentProduct(
        Interceptor $productInfo,
        $requestInfo = null
    ) {
        $productPlan = $this->planService->findByProductId($requestInfo['product']);

        $currentProduct = new CurrentProduct();

        $quantity = 1;
        if (isset($requestInfo['qty'])) {
            $quantity = $requestInfo['qty'];
        }

        $currentProduct->setQuantity($quantity);

        if ($productPlan !== null) {
            $currentProduct->setIsNormalProduct(false);
            $currentProduct->setProductPlanSelected($productPlan);
            return $currentProduct;
        }

        $isNormalProduct = $this->checkIsNormalProduct($requestInfo);
        if ($isNormalProduct) {
            $currentProduct->setIsNormalProduct($isNormalProduct);
            return $currentProduct;
        }

        $repetitionSelected = $this->getOptionRecurrenceSelected(
            $productInfo->getOptions(),
            $requestInfo['options']
        );

        if (!$repetitionSelected) {
            $currentProduct->setIsNormalProduct(true);
            return $currentProduct;
        }

        $currentProduct->setRepetitionSelected($repetitionSelected);

        $productSubscriptionSelected =
            $this->productSubscriptionService->findById(
                $repetitionSelected->getSubscriptionId()
            );

        $currentProduct->setProductSubscriptionSelected(
            $productSubscriptionSelected
        );

        return $currentProduct;
    }

    protected function buildProductListInCart(Cart $cart)
    {
        $productList = new ProductListInCart();

        $itemQuoteList = $cart->getQuote()->getAllVisibleItems();
        foreach ($itemQuoteList as $item) {

            $productPlan = $this->planService->findByProductId(
                $item->getProduct()->getId()
            );

            if ($productPlan !== null) {
                $productList->addProductPlan($productPlan);
                continue;
            }

            $repetitionInCart = $this->recurrenceProductHelper->getSelectedRepetition(
                $item
            );

            if (is_null($repetitionInCart)) {
                $productList->addNormalProducts($item);
                continue;
            }

            $productSubscriptionInCart =
                $this->productSubscriptionService->findById(
                    $repetitionInCart->getSubscriptionId()
                );

            $productList->setRepetition($repetitionInCart);
            $productList->setRecurrenceProduct($productSubscriptionInCart);
            $productList->addRecurrenceProduct($productSubscriptionInCart);
        }

        return $productList;
    }

    /**
     * @param array $requestInfo
     * @return bool
     */
    public function checkIsNormalProduct($requestInfo)
    {
        if (!isset($requestInfo['options'])) {
            return true;
        }
        return false;
    }

    /**
     * @param Option[] $optionsList
     * @param array $optionsSelected
     * @return Repetition|null
     */
    public function getOptionRecurrenceSelected(array $optionsList, array $optionsSelected)
    {
        $productOptionValue = null;
        foreach ($optionsList as $option) {
            if ($option->getSku() != 'recurrence') {
                continue;
            }

            /* @var Value[]|ProductCustomOptionValuesInterface[] $valueList */
            $valueList = $option->getValues();
            $productOptionValue = $this->getOptionsValues($valueList, $optionsSelected);
        }

        if (is_null($productOptionValue)) {
            return null;
        }

        return $this->repetitionService->getRepetitionById(
            $productOptionValue->getSortOrder()
        );
    }

    /**
     * @param Value[] $valueList
     * @param array $optionsSelected
     * @return Value|null
     */
    private function getOptionsValues(array $valueList, array $optionsSelected)
    {
        $optionValueSelected = null;
        foreach ($valueList as $value) {
            $optionValueSelected = $this->getOptionValueSelected($value, $optionsSelected);
            if (!is_null($optionValueSelected)) {
                return $optionValueSelected;
            }
        }

        return $optionValueSelected;
    }

    /**
     * @param Value $value
     * @param array $optionsSelected
     * @return Value|null
     */
    private function getOptionValueSelected(Value $value, array $optionsSelected)
    {
        $optionValueSelected = null;
        foreach ($optionsSelected as $optionId => $optionTypeId) {
            if (($value->getOptionTypeId() == $optionTypeId) &&
                ($value->getData()['option_id'] == $optionId)) {
                $optionValueSelected = $value;
            }
        }

        return $optionValueSelected;
    }
}
