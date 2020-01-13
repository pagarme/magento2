<?php

namespace MundiPagg\MundiPagg\Plugin\Princing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Catalog\Model\Product\Interceptor as ProductInterceptor;

class FinalPricePlugin
{
    /**
     * FinalPricePlugin constructor.
     */
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
    }

    /**
     * @param FinalPriceBox $subject
     * @param $template
     * @return array
     */
    public function beforeSetTemplate(FinalPriceBox $subject, $template)
    {
        $a = 1;
        if (MPSetup::getModuleConfiguration()->isEnabled()) {
            return ['MundiPagg_MundiPagg::product/priceFinal.phtml'];    
        }
        
        return [$template];
    }

    /**
     * @return int|null
     */
    public static function getMaxInstallments()
    {
        $list‌cardConfig = MPSetup::getModuleConfiguration()->getCardConfigs();

        $installment = null;
        foreach ($list‌cardConfig as $cardConfig) {
            if ($cardConfig->getBrand()->getName() != 'noBrand' || !$cardConfig->isEnabled()) {
                continue;
            }

            $installment = $cardConfig->getMaxInstallment();
        }

        return $installment;
    }

    /**
     * @param int $productId
     * @return false|string|null
     */
    public static function getPriceRecurrence($productId)
    {
        $productSubscriptionService = new ProductSubscriptionService();
        $productSubscription = $productSubscriptionService->findByProductId($productId);

        if (is_null($productSubscription)) {
            return null;
        }

        $objectManager = ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')
            ->load($productId);

        $currency = self::getMostLowPriceRecurrence($productSubscription, $product);

        $numberFormatter = new \NumberFormatter(
            'pt-BR',
            \NumberFormatter::CURRENCY
        );

        return $numberFormatter->format($currency);
    }

    /**
     * @param ProductSubscription $productSubscription
     * @param ProductInterceptor $product
     * @return float
     */
    private static function getMostLowPriceRecurrence(
        ProductSubscription $productSubscription,
        ProductInterceptor $product
    ) {
        $prices = [];
        foreach ($productSubscription->getRepetitions() as $repetition) {
            $recurrencePrice = $repetition->getRecurrencePrice();

            if ($recurrencePrice == 0) {
                $recurrencePrice = $product->getPrice();
            }

            $prices[] = ($recurrencePrice / $repetition->getIntervalCount());
        }

        return min($prices) / 100;
    }
}
