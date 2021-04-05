<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\RepetitionService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Api\Data\ProductInterface;

class ProductSubscriptionHelper extends AbstractHelper
{
    /**
     * @var LocalizationService
     */
    protected $i18n;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @param ProductSubscription $productSubscription
     */
    public function deleteRecurrenceCustomOption(ProductSubscription $productSubscription)
    {
        $productId = $productSubscription->getProductId();

        /**
         * @var Product $product
         */
        $product = $this->objectManager->get(Product::class)->load($productId);

        $customOptions = [];
        $options = $product->getOptions();
        foreach ($options as $option) {
            if ($option->getSku() == "recurrence") {
                continue;
            }
            $customOptions[] = $option;
        }

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);
        $product->setOptions($customOptions)->save();
    }

    /**
     * @param ProductSubscription $productSubscription
     * @throws \Exception
     */
    public function setCustomOption(ProductSubscription $productSubscription)
    {
        $productId = $productSubscription->getProductId();
        $product = $this->getProductPlataform($productId);

        $values = $this->getValuesFromRepetitions($productSubscription);

        /**
         * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption
         */
        $customOption = $this->objectManager->create(
            'Magento\Catalog\Api\Data\ProductCustomOptionInterface'
        );

        $customOption->setTitle('Cycles')
            ->setType('radio')
            ->setIsRequire(true)
            ->setSortOrder(100)
            ->setPrice(0)
            ->setPriceType('fixed')
            ->setValues($values)
            ->setMaxCharacters(50)
            ->setSku("recurrence")
            ->setProductSku($product->getSku());

        $customOptions = $this->addCustomOptionOnArray($customOption, $product);

        $this->keeProductConfiguration($product);

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);
        $product->setOptions($customOptions)->save();
    }

    /**
     * @param Product $product
     */
    private function keeProductConfiguration(Product $product)
    {
        /**
         * @var ScopeOverriddenValue $scopeOverriddenValue
         */
        $scopeOverriddenValue = $this->objectManager->get(ScopeOverriddenValue::class);

        $forceStoreId = false;

        $listItemsOverrideStore = [
            'status',
            'name',
            'tax_class_id',
            'visibility',
            'url_key',
            'meta_title',
            'meta_description',
            'options_container',
            'msrp_display_actual_price_type'
        ];

        foreach ($listItemsOverrideStore as $attributeName) {
            $isOverriden = $scopeOverriddenValue->containsValue(
                ProductInterface::class,
                $product,
                $attributeName,
                $product->getStoreId()
            );

            if (!$isOverriden) {
                $forceStoreId = true;
                break;
            }
        }

        if ($forceStoreId) {
            $product->setStoreId(0);
        }
    }

    /**
     * @param $customOption
     * @param $product
     * @return array
     */
    protected function addCustomOptionOnArray($customOption, $product)
    {
        $options = $product->getOptions();

        if (empty($options)) {
            return [$customOption];
        }

        $customOptions = [];
        $hasRecurrenceOption = false;
        foreach ($options as $option) {
            if ($option->getSku() !== "recurrence") {
                $customOptions[] = $option;
                continue;
            }
            $customOptions[] = $customOption;
            $hasRecurrenceOption = true;
        }

        if (!$hasRecurrenceOption) {
            $customOptions[] = $customOption;
        }
        return $customOptions;
    }

    /**
     * @param ProductSubscription $productSubscription
     * @return array
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    protected function getValuesFromRepetitions(ProductSubscription $productSubscription)
    {
        $values = [];

        $sellAsNormalProduct = [
            "title" => "Compra Única",
            "price" => 0,
            "price_type" => "fixed",
            "sort_order" => "0"
        ];

        if (!empty($productSubscription->getSellAsNormalProduct())) {
            $values[] = $sellAsNormalProduct;
        }

        $repetitionService = new RepetitionService();

        $repetitions = $productSubscription->getRepetitions();
        foreach ($repetitions as $repetition) {
            $values[] = [
                "title" => $repetitionService->getCycleTitle($repetition),
                "price" => 0,
                "price_type" => "fixed",
                "sort_order" => $repetition->getId()
            ];
        }

        return $values;
    }

    /**
     * @param int $productId
     * @return Product
     * @throws \Exception
     */
    public function getProductPlataform($productId)
    {
        /**
         * @var Product $product
         */
        $product = $this->objectManager->get(Product::class)
            ->load($productId);

        if ($product->getId() === null) {
            throw new \Exception('product not found', 404);
        }

        return $product;
    }
}
