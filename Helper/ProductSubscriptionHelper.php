<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Magento\Framework\App\ObjectManager;

class ProductSubscriptionHelper extends AbstractHelper
{
    /**
     * @var LocalizationService
     */
    protected $i18n;

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
    }

    public function deleteRecurrenceCustomOption(ProductSubscription $productSubscription)
    {
        $objectManager = ObjectManager::getInstance();

        $productId = $productSubscription->getProductId();
        $product = $objectManager->get('Magento\Catalog\Model\Product')
            ->load($productId);

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
        $objectManager = ObjectManager::getInstance();

        $productId = $productSubscription->getProductId();
        $product = $this->getProductPlataform($productId);

        $values = $this->getValuesFromRepetitions($productSubscription);

        $customOption = $objectManager->create(
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

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);
        $product->setOptions($customOptions)->save();
    }

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

    protected function getValuesFromRepetitions(ProductSubscription $productSubscription)
    {
        $values = [];

        $sellAsNormalProduct = [
            "title" => "Compra Única",
            "price" => 0,
            "price_type"  => "fixed",
            "sort_order"  => "0"
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
                "price_type"  => "fixed",
                "sort_order"  => $repetition->getId()
            ];
        }

        return $values;
    }

    /**
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     * @throws \Exception
     */
    public function getProductPlataform($productId)
    {
        $objectManager = ObjectManager::getInstance();

        $product = $objectManager->get('Magento\Catalog\Model\Product')
            ->load($productId);

        if ($product->getId() === null) {
            throw new \Exception('product not found', 404);
        }

        return $product;
    }
}
