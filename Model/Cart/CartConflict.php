<?php

namespace MundiPagg\MundiPagg\Model\Cart;

use Magento\Checkout\Model\Cart;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use Mundipagg\Core\Recurrence\Services\Rules\CompatibleRecurrenceProducts;
use Mundipagg\Core\Recurrence\Services\Rules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\Rules\MoreThanOneRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\Rules\NormalWithRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\Rules\ProductListInCart;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Magento\Catalog\Model\Product\Interceptor;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Magento\Framework\Exception\LocalizedException;

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
     * CartConflict constructor.
     */
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->repetitionService = new RepetitionService();
        $this->recurrenceService = new RecurrenceService();
        $this->recurrenceProductHelper = new RecurrenceProductHelper();
        $this->productSubscriptionService = new ProductSubscriptionService();
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
            empty($productListInCart->getNormalProducts()) &&
            empty($productListInCart->getRecurrenceProducts())
        ) {
            return [$productInfo, $requestInfo];
        }

        $cartRules = $this->getRules();
        foreach ($cartRules as $rule) {
            $rule->run($currentProduct, $productListInCart);

            $error = $rule->getError();
            if (!empty($error)) {
                throw new LocalizedException(__($error));
            }
        }

        return [$productInfo, $requestInfo];
    }

    protected function getRules()
    {
        $recurrenceConfiguration = MPSetup::getModuleConfiguration()
            ->getRecurrenceConfig();

        return [
            new NormalWithRecurrenceProduct($recurrenceConfiguration),
            new MoreThanOneRecurrenceProduct($recurrenceConfiguration),
            new CompatibleRecurrenceProducts()
        ];
    }

    protected function buildCurrentProduct(
        Interceptor $productInfo,
        $requestInfo = null
    ) {

        $currentProduct = new CurrentProduct();

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

    protected function buildProductListInCart(Cart $cart) {

        $productList = new ProductListInCart();

        $itemQuoteList = $cart->getQuote()->getAllVisibleItems();
        foreach ($itemQuoteList as $item) {

            $repetitionInCart =
                $this->recurrenceProductHelper->getSelectedRepetition(
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
