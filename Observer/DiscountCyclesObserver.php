<?php

namespace Pagarme\Pagarme\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Pagarme\Pagarme\Helper\ProductHelper;

class DiscountCyclesObserver implements ObserverInterface
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    /**
     * DiscountCyclesObserver constructor.
     * @param RecurrenceProductHelper $recurrenceProductHelper
     * @throws Exception
     */
    public function __construct(
        \Pagarme\Pagarme\Helper\ProductHelper $productHelper
    ) {
        Magento2CoreSetup::bootstrap();
        $this->productHelper = $productHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (isset($observer->getProduct()->getOptions()[0])) {
            $this->applyDiscountCycles(
                $observer->getProduct()->getOptions()[0]->getValues(),
                $observer->getProduct()
            );
        }
    }

    /**
     * @param Cycles $cycles
     * @param Product $product
     */
    private function applyDiscountCycles($cycles, $product)
    {
        foreach ($cycles as $cycle) {
            $cycle->setTitle(ProductHelper::applyDiscount($cycle->getTitle(), $product));
        }
    }
}