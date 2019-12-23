<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mundipagg\Core\Kernel\Services\MoneyService;
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
    /**
     * @var MoneyService
     */
    protected $moneyService;

    public function __construct(RecurrenceProductHelper $recurrenceProductHelper)
    {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->moneyService = new MoneyService();
    }

    public function execute(Observer $observer)
    {
        /* @var $item Mage_Sales_Model_Quote_Item */
        $item = $observer->getQuoteItem();
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $productSubscription = $this->getSubscriptionProduct($item);

        if (!$productSubscription) {
            return;
        }

        $specialPrice = $this->getPriceFromRepetition($item);

        if ($specialPrice > 0) {
            $item->setCustomPrice($specialPrice);
            $item->setOriginalCustomPrice($specialPrice);
            $item->getProduct()->setIsSuperMode(true);
        }
    }

    public function getPriceFromRepetition($item)
    {
        $repetition = $this->recurrenceProductHelper
            ->getRepetitionSelected($item);

        if (!empty($repetition) && $repetition->getRecurrencePrice() > 0) {
            return $this->moneyService->centsToFloat(
                $repetition->getRecurrencePrice()
            );
        }

        return 0;
    }

    public function getSubscriptionProduct($item)
    {
        $productId = $item->getProductId();
        $productSubscriptionService = new ProductSubscriptionService();
        $productSubscription =
            $productSubscriptionService->findByProductId($productId);

        if ($productSubscription) {
            return $productSubscription;
        }

        return null;
    }
}