<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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

    /**
     * @param Repetition $repetition
     * @return string
     */
    public function tryFindDictionaryEventCustomOptionsProductSubscription(
        Repetition $repetition
    ) {
        $dictionary = [
            'month' => [
                1 => 'monthly',
                2 => 'bimonthly',
                3 => 'quarterly',
                6 => 'semiannual'
            ],
            'year' => [
                1 => 'yearly',
                2 => 'biennial'
            ],
            'week' => [
                1 => 'weekly'
            ]
        ];

        $intervalType = $repetition->getInterval();
        $intervalCount = $repetition->getIntervalCount();

        if (isset($dictionary[$intervalType][$intervalCount])) {
            return $this->i18n->getDashboard($dictionary[$intervalType][$intervalCount]);
        }

        $intervalType = $this->i18n->getDashboard($repetition->getIntervalTypeLabel());
        return "De {$intervalCount} em {$intervalCount} {$intervalType}";
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

    public function setCustomOption(ProductSubscription $productSubscription)
    {
        $objectManager = ObjectManager::getInstance();

        $productId = $productSubscription->getProductId();
        $product = $objectManager->get('Magento\Catalog\Model\Product')
            ->load($productId);

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

        $repetitions = $productSubscription->getRepetitions();
        foreach ($repetitions as $repetition) {
            $values[] = [
                "title" => $this->getCycleTitle($repetition),
                "price" => 0,
                "price_type"  => "fixed",
                "sort_order"  => $repetition->getId()
            ];
        }

        return $values;
    }

    public function getCycleTitle(Repetition $repetition)
    {
        $intervalLabel = $this->tryFindDictionaryEventCustomOptionsProductSubscription($repetition);

        if ($repetition->getRecurrencePrice() <= 0) {
            return $intervalLabel;
        }

        $totalAmount = $this->moneyService->centsToFloat(
            $repetition->getRecurrencePrice()
        );

        return $intervalLabel . " - (Total: R$ {$totalAmount})";
    }
}
