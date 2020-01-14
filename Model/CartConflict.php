<?php

namespace MundiPagg\MundiPagg\Model;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Magento\Catalog\Model\Product\Interceptor;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\Services\RulesCheckoutService;

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
     * @var RulesCheckoutService
     */
    private $rulesCheckoutService;

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
        $this->rulesCheckoutService = new RulesCheckoutService();
        $this->productSubscriptionService = new ProductSubscriptionService();
    }

    public function beforeAddProduct(
        Cart $cart,
        Interceptor $productInfo,
        $requestInfo = null
    ) {
        $normalProduct = $this->checkIsNormalProduct($requestInfo);
        if ($normalProduct) {
            return [$productInfo, $requestInfo];
        }

        $repetitionSelected = $this->getOptionRecurrenceSelected(
            $productInfo->getOptions(),
            $requestInfo['options']
        );

        if (is_null($repetitionSelected)) {
            return [$productInfo, $requestInfo];
        }

        $productSubscriptionSelected = $this->productSubscriptionService->findById(
            $repetitionSelected->getSubscriptionId()
        );

        /* @var Item[] $itemQuoteList */
        $itemQuoteList = $cart->getQuote()->getAllVisibleItems();
        foreach ($itemQuoteList as $item) {
            $repetitionInCart = $this->recurrenceProductHelper->getSelectedRepetition(
                $item
            );

            if (is_null($repetitionInCart)) {
                continue;
            }

            $productSubscriptionInCart = $this->productSubscriptionService->findById(
                $repetitionInCart->getSubscriptionId()
            );

            $passRules = $this->rulesCheckoutService->runRulesCheckoutSubscription(
                $productSubscriptionInCart,
                $productSubscriptionSelected,
                $repetitionInCart,
                $repetitionSelected
            );

            if(!$passRules) {
                $messageConflictRecurrence = MPSetup::getModuleConfiguration()
                    ->getRecurrenceConfig()
                    ->getCheckoutConflictMessage();

                throw new LocalizedException(__($messageConflictRecurrence));
            }
        }

        return [$productInfo, $requestInfo];
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
