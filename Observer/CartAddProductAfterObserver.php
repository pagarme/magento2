<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;

class CartAddProductAfterObserver implements ObserverInterface
{
    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    public function __construct(RecurrenceProductHelper $recurrenceProductHelper)
    {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
    }

    public function execute(Observer $observer)
    {
        /* @var $item Mage_Sales_Model_Quote_Item */
        $item = $observer->getQuoteItem();
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $recurrenceProduct = $this->getSubscriptionProduct($item);

        if (!$recurrenceProduct) {
            return;
        }

        $specialPrice = $this->getSpecialPrice($item);

        if ($specialPrice > 0) {
            $item->setCustomPrice($specialPrice);
            $item->setOriginalCustomPrice($specialPrice);
            $item->getProduct()->setIsSuperMode(true);
        }
    }

    public function getSpecialPrice($item)
    {
        $discountObject = $this->getDiscountFromRepetition($item);
        if (!$discountObject) {
            return null;
        }

        $product = $item->getProduct();
        $price = $product->getFinalPrice();

        $flat = DiscountValueObject::DISCOUNT_TYPE_FLAT;
        if ($discountObject->getDiscountType() == $flat) {
            return $price - $discountObject->getDiscountValue();
        }

        $percentDiscount = $discountObject->getDiscountValue() / 100;
        return $price - ($price * $percentDiscount);
    }

    public function getDiscountFromRepetition($item)
    {
        $repetition = $this->recurrenceProductHelper->getRepetitionSelected($item);

        if (!empty($repetition)) {
            return $repetition->getDiscount();
        }

        return null;
    }

    public function getSubscriptionProduct($item)
    {
        $productId = $item->getProductId();
        $productSubscriptionService = new ProductSubscriptionService();
        $productSubscription = $productSubscriptionService->findByProductId($productId);

        if ($productSubscription) {
            return $productSubscription;
        }

        return null;
    }
}