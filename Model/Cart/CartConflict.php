<?php

namespace Pagarme\Pagarme\Model\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;
use Pagarme\Core\Recurrence\Services\CartRules\CurrentProduct;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Core\Recurrence\Services\RepetitionService;
use Pagarme\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Pagarme\Core\Recurrence\Services\CartRules\NormalWithRecurrenceProduct;
use Pagarme\Core\Recurrence\Services\CartRules\ProductListInCart;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Magento\Catalog\Model\Product\Interceptor;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Core\Recurrence\Services\PlanService;
use Pagarme\Core\Recurrence\Services\CartRules\JustProductPlanInCart;
use Pagarme\Core\Recurrence\Services\CartRules\JustSelfProductPlanInCart;
use Pagarme\Pagarme\Helper\RulesCartRun;
use Pagarme\Core\Kernel\Aggregates\Configuration;

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
     * @var Configuration
     */
    private $pagarmeConfig;

    /**
     * CartConflict constructor.
     * @throws \Exception
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
        $this->pagarmeConfig = Magento2CoreSetup::getModuleConfiguration();
    }

    /**
     * @param Cart $cart
     * @param $dataQty
     * @throws LocalizedException
     */
    public function beforeUpdateItems(Cart $cart, $dataQty)
    {
        if (
            !$this->pagarmeConfig->isEnabled() ||
            !$this->pagarmeConfig->getRecurrenceConfig()->isEnabled()
        ) {
            return;
        }

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
                $message = $i18n->getDashboard(
                    'You must have only one product plan in the cart'
                );

                throw new LocalizedException(__($message));
            }
        }
    }

    /**
     * @param Cart $cart
     * @param Interceptor $productInfo
     * @param mixed|null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Cart $cart,
        Interceptor $productInfo,
        $requestInfo = null
    ) {
        if (
            !$this->pagarmeConfig->isEnabled() ||
            !$this->pagarmeConfig->getRecurrenceConfig()->isEnabled()
        ) {
            return [$productInfo, $requestInfo];
        }

        $currentProduct = $this->buildCurrentProduct(
            $productInfo,
            $requestInfo
        );

        $productListInCart = $this->buildProductListInCart($cart);

        $this->rulesCartRun->runRulesProductPlan(
            $currentProduct,
            $productListInCart
        );

        $this->rulesCartRun->runRulesProductSubscription(
            $currentProduct,
            $productListInCart
        );

        return [$productInfo, $requestInfo];
    }

    /**
     * @param Interceptor $productInfo
     * @param mixed|null $requestInfo
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

    /**
     * @param Cart $cart
     * @return ProductListInCart
     */
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

            $productSubscriptionInCart = $this->productSubscriptionService->findById(
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
