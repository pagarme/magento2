<?php

namespace Pagarme\Pagarme\Plugin\Princing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Catalog\Model\Product\Interceptor as ProductInterceptor;
use Pagarme\Core\Recurrence\Aggregates\Repetition;

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
        $moduleConfiguration = MPSetup::getModuleConfiguration();

        if (
            $moduleConfiguration->isEnabled() &&
            $moduleConfiguration->getRecurrenceConfig()->isEnabled() &&
            $moduleConfiguration->getRecurrenceConfig()->isShowRecurrenceCurrencyWidget()
        ) {
            return ['Pagarme_Pagarme::product/priceFinal.phtml'];
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

        $currency = self::getLowestRecurrencePrice($subscriptionProduct, $product);

        $numberFormatter = new \NumberFormatter(
            'pt-BR',
            \NumberFormatter::CURRENCY
        );

        $currency['price'] = $numberFormatter->format($currency['price']);

        return $currency;
    }

    /**
     * @param ProductSubscription $subscriptionProduct
     * @param ProductInterceptor $product
     * @return float
     */
    private static function getLowestRecurrencePrice(
        ProductSubscription $subscriptionProduct,
        ProductInterceptor $product
    ) {
        $prices = [];
        foreach ($subscriptionProduct->getRepetitions() as $repetition) {
            $recurrencePrice = $repetition->getRecurrencePrice();

            if ($recurrencePrice == 0) {
                $recurrencePrice = ($product->getPrice() * 100);
            }

            $price = $recurrencePrice / $repetition->getIntervalCount() / 100;

            if ($repetition->getInterval() == Repetition::INTERVAL_YEAR) {
                $price = $recurrencePrice / (12 * $repetition->getIntervalCount());
            }

            $prices[$price] = [
                'price' => $price,
                'interval' => $repetition->getInterval(),
                'intervalCount' => $repetition->getIntervalCount()
            ];
        }
        ksort($prices);

        return reset($prices);
    }
}
