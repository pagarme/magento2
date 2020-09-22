<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Magento\Quote\Model\Quote\Item;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;

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

    /**
     * @var Configuration
     */
    protected $mundipaggConfig;

    public function __construct(RecurrenceProductHelper $recurrenceProductHelper)
    {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->moneyService = new MoneyService();
        $this->mundipaggConfig = Magento2CoreSetup::getModuleConfiguration();
    }

    /**
     * @param Observer $observer
     * @throws InvalidParamException
     */
    public function execute(Observer $observer)
    {
        if (
            !$this->mundipaggConfig->isEnabled() ||
            !$this->mundipaggConfig->getRecurrenceConfig()->isEnabled()
        ) {
            return;
        }

        /* @var Item $item */
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

    /**
     * @param Item $item
     * @return float|int
     * @throws InvalidParamException
     */
    public function getPriceFromRepetition(Item $item)
    {
        $repetition = $this->recurrenceProductHelper
            ->getSelectedRepetition($item);

        if (!empty($repetition) && $repetition->getRecurrencePrice() > 0) {
            return $this->moneyService->centsToFloat(
                $repetition->getRecurrencePrice()
            );
        }

        return 0;
    }

    /**
     * @param Item $item
     * @return ProductSubscription|null
     */
    public function getSubscriptionProduct(Item $item)
    {
        $productId = $item->getProductId();
        $productSubscriptionService = new ProductSubscriptionService();

        /**
         * @var ProductSubscription $productSubscription
         */
        $productSubscription = $productSubscriptionService->findByProductId($productId);

        if ($productSubscription) {
            return $productSubscription;
        }

        return null;
    }
}