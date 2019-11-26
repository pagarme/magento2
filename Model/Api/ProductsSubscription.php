<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Api\ProductSubscriptionApiInterface;
use \Magento\Framework\Webapi\Rest\Request;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class ProductsSubscription implements ProductSubscriptionApiInterface
{

    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
    }

    /**
     * Returns greeting message to user
     *
     * @param ProductSubscriptionInterface $productSubscription
     * @return mixed
     */
    public function save(ProductSubscriptionInterface $productSubscription)
    {
        try {
            $productSubscriptionService = new ProductSubscriptionService();
            $productSubscription =
                $productSubscriptionService->saveProductSubscription($productSubscription);

            $this->setCustomOption($productSubscription);

        } catch (\Exception $exception) {
            return [
                'code' => 404,
                'message' => $exception->getMessage()
            ];
        }

        return [
            'code' => 200,
            'message' => 'Product subscription saved'
        ];
    }

    protected function setCustomOption(ProductSubscription $productSubscription)
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

    protected function getCycleTitle(Repetition $repetition)
    {
        $intervalCount = $repetition->getIntervalCount();
        $intervalType = $this->i18n->getDashboard(
            $repetition->getIntervalTypeLabel()
        );

        $totalAmount = $this->moneyService->centsToFloat(
            $repetition->getRecurrencePrice()
        );

        $discountLabel = " - (Total: R$ {$totalAmount})";
        // @todo create dictionary
        $intervalLabel = "De {$intervalCount} em {$intervalCount} {$intervalType}";

        if (empty($repetition->getRecurrencePrice())) {
            return $intervalLabel;
        }
        return $intervalLabel . $discountLabel;
    }
}