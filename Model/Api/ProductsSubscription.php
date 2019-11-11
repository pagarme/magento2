<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use MundiPagg\MundiPagg\Api\ProductSubscriptionInterface;
use \Magento\Framework\Webapi\Rest\Request;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class ProductsSubscription implements ProductSubscriptionInterface
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
    }
    /**
     * Returns greeting message to user
     *
     * @param mixed $data
     * @return mixed
     */
    public function saveProductSubscription()
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);

        if (empty($params)) {
            return json_encode([
                'code' => 404,
                'message' => 'Error on save product subscription'
            ]);
        }

        $productSubscriptionService = new ProductSubscriptionService();
        $productSubscription = $productSubscriptionService->saveProductSubscription($params['form']);
        $this->setCustomOption($productSubscription);

        return json_encode([
            'code' => 200,
            'message' => 'Product subscription saved'
        ]);
    }

    protected function setCustomOption(ProductSubscription $productSubscription)
    {
        $objectManager = ObjectManager::getInstance();

        $productId = $productSubscription->getProductId();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);

        $values = $this->getValuesFromRepetitions($productSubscription->getRepetitions());

        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
        $customOption = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterface');
        $customOption->setTitle('Cycles')
            ->setType('radio')
            ->setIsRequire(true)
            ->setSortOrder(1)
            ->setPrice(0)
            ->setPriceType('fixed')
            ->setValues($values)
            ->setMaxCharacters(50)
            ->setProductSku($product->getSku());

        $customOptions[] = $customOption;

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);
        $product->setOptions($customOptions)->save();
    }

    protected function getValuesFromRepetitions($repetitions)
    {
        $values = [];
        $sortOrder = 1;
        foreach ($repetitions as $repetition) {
            $values[] = [
                "title" => $this->getCycleTitle($repetition),
                "price" => 0.0,
                "price_type"  => "fixed",
                "sort_order"  => $sortOrder
            ];
            $sortOrder++;
        }

        return $values;
    }

    protected function getCycleTitle(Repetition $repetition)
    {
        $intervalCount = $repetition->getIntervalCount();
        $intervalType = $this->i18n->getDashboard(
            $repetition->getIntervalTypeLabel()
        );
        $discount = $this->getDiscountFormatted($repetition);

        return "De $intervalCount em $intervalCount $intervalType - $discount de desconto";
    }

    protected function getDiscountFormatted(Repetition $repetition)
    {
        $discountValue = $repetition->getDiscountValue();
        $discountType = $repetition->getDiscountType();
        $symbols = $repetition->getDiscountTypeSymbols();
        $flat = DiscountValueObject::DISCOUNT_TYPE_FLAT;

        if ($repetition->getDiscount()->getDiscountType() == $flat) {
            return implode(" ", [
                $symbols[$discountType],
                $discountValue
            ]);
        }

        return implode("", [
            $discountValue,
            $symbols[$discountType]
        ]);
    }
}