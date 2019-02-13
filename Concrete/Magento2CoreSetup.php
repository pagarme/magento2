<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\Config as Magento2StoreConfig;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Factories\ConfigurationFactory;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Helper\ModuleHelper;
use MundiPagg\MundiPagg\Model\Enum\CreditCardBrandEnum;

final class Magento2CoreSetup extends AbstractModuleCoreSetup
{
    const MODULE_NAME = 'MundiPagg_MundiPagg';

    static protected function setModuleVersion()
    {
        $objectManager = ObjectManager::getInstance();
        $moduleHelper = $objectManager->get(ModuleHelper::class);

        self::$moduleVersion = $moduleHelper->getVersion(self::MODULE_NAME);
    }

    static protected function setLogPath()
    {
        //@todo get this from config.
        self::$logPath = 'var/log';
    }

    static protected function setConfig()
    {
        self::$config = [
            AbstractModuleCoreSetup::CONCRETE_DATABASE_DECORATOR_CLASS =>
                Magento2DatabaseDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS =>
                Magento2PlatformOrderDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS =>
                Magento2PlatformInvoiceDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_CREDITMEMO_DECORATOR_CLASS =>
                Magento2PlatformCreditmemoDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_DATA_SERVICE =>
                Magento2DataService::class
        ];
    }

    static public function getDatabaseAccessObject()
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        return $resource;
    }

    static protected function getPlatformHubAppPublicAppKey()
    {
        /** @todo get the correct key for magento2 */
        return "2d2db409-fed0-4bd8-ac1e-43eeff33458d";
    }

    static public function _getDashboardLanguage()
    {
        $objectManager = ObjectManager::getInstance();
        $resolver = $objectManager->get('Magento\Framework\Locale\Resolver');

        return $resolver->getLocale();
    }

    static public function _getStoreLanguage()
    {
        /**
         * @todo verify if this work as expected in the store screens.
         *       On dashboard, this will return null.
         */
        $objectManager = ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Api\Data\StoreInterface');

        return $store->getLocaleCode();
    }

    protected static function loadModuleConfiguration()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var  Config $platformBaseConfig
         */
        $platformBaseConfig = $objectManager->get(Config::class);
        /** @var Magento2StoreConfig $storeConfig */
        $storeConfig = $objectManager->get(Magento2StoreConfig::class);

        $configData = new \stdClass;
        $configData->boletoEnabled = false;
        $configData->creditCardEnabled = false;
        $configData->boletoCreditCardEnabled = false;
        $configData->twoCreditCardsEnabled = false;
        $configData->hubInstallId = null;

        $configData->testMode = $platformBaseConfig->getTestMode();
        $configData->keys = [
            Configuration::KEY_PUBLIC => $platformBaseConfig->getPublicKey(),
            Configuration::KEY_SECRET => $platformBaseConfig->getSecretKey(),
        ];


        $configData->cardConfigs = self::getCardConfigs($storeConfig);

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }

    static private function getCardConfigs($storeConfig)
    {
        $brands = array_merge([''],explode(
            ',',
            $storeConfig->getValue('payment/mundipagg_creditcard/cctypes')
        ));

        $cardConfigs = [];
        foreach ($brands as $brand)
        {
            $brand = "_" . strtolower($brand);
            $brandMethod = str_replace('_','', $brand);
            $adapted = self::getBrandAdapter(strtoupper($brandMethod));
            if ($adapted !== false) {
                $brand = "_" . strtolower($adapted);
                $brandMethod = str_replace('_','', $brand);
            }

            if ($brandMethod == '')
            {
                $brand = '';
                $brandMethod = 'nobrand';
            }

            $interestByBrand =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_by_issuer' . $brand);
            if ($interestByBrand != 1) {
                $brand = '';
            }

            $max =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_number' . $brand);
            $minValue =  $storeConfig->getValue('payment/mundipagg_creditcard/installment_min_amount' . $brand);
            $initial =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_rate_initial' . $brand);
            $incremental =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_rate_incremental'. $brand);
            $maxWithout =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_max_without_interest' . $brand);

            $cardConfigs[] = new CardConfig(
                true,
                CardBrand::$brandMethod(),
                $max,
                $maxWithout,
                $initial,
                $incremental,
                ($minValue !== null ? $minValue : 0) * 100
            );
        }
        return $cardConfigs;
    }

    /** @see AbstractRequestDataProvider::getBrandAdapter() */
    private static function getBrandAdapter($brand)
    {
        $fromTo = [
            'VI' => CreditCardBrandEnum::VISA,
            'MC' => CreditCardBrandEnum::MASTERCARD,
            'AE' => CreditCardBrandEnum::AMEX,
            'DI' => CreditCardBrandEnum::DISCOVER,
            'DN' => CreditCardBrandEnum::DINERS,
        ];

        return (isset($fromTo[$brand])) ? $fromTo[$brand] : false;
    }

    protected static function _formatToCurrency($price)
    {
        $objectManager = ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');

        return $priceHelper->currency($price, true, false);
    }
}