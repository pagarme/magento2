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
use Mundipagg\Core\Recurrence\Aggregates\Repetition;

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
        $moduleEnabled = MPSetup::getModuleConfiguration()->isEnabled();
        $showCurrencyWidget = MPSetup::getModuleConfiguration()
            ->getRecurrenceConfig()
            ->isShowRecurrenceCurrencyWidget();

        if ($moduleEnabled && $showCurrencyWidget) {
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
            if (
                $cardConfig->getBrand()->getName() != 'noBrand' ||
                !$cardConfig->isEnabled()
            ) {
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
    public static function getRecurrencePrice($productId)
    {
        $subscriptionProductService = new ProductSubscriptionService();
        $subscriptionProduct = $subscriptionProductService->findByProductId($productId);

        if (is_null($subscriptionProduct)) {
            return null;
        }

        $objectManager = ObjectManager::getInstance();
        $product = $objectManager->create(Product::class)
            ->load($productId);

        $currency = self::getLowPriceRecurrence($subscriptionProduct, $product);

        $numberFormatter = new \NumberFormatter(
            'pt-BR',
            \NumberFormatter::CURRENCY
        );

        return $numberFormatter->format($currency);
    }

    /**
     * @param ProductSubscription $subscriptionProduct
     * @param ProductInterceptor $product
     * @return float
     */
    private static function getLowPriceRecurrence(
        ProductSubscription $subscriptionProduct,
        ProductInterceptor $product
    ) {
        $prices = [];
        foreach ($subscriptionProduct->getRepetitions() as $repetition) {
            $recurrencePrice = $repetition->getRecurrencePrice();

            if ($recurrencePrice == 0) {
                $recurrencePrice = ($product->getPrice() * 100);
            }

            if ($repetition->getInterval() == Repetition::INTERVAL_YEAR) {
                $prices[] = ($recurrencePrice / (12 * $repetition->getIntervalCount()));
                continue;
            }

            $prices[] = ($recurrencePrice / $repetition->getIntervalCount());
        }

        return min($prices) / 100;
    }
}
