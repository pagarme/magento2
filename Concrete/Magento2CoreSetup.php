<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Factories\ConfigurationFactory;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;

final class Magento2CoreSetup extends AbstractModuleCoreSetup
{
    static protected function setModuleVersion()
    {
        //@todo get the correct number;
        self::$moduleVersion = '2.14.233';
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

        $configData = new \stdClass;
        $configData->cardConfigs = [];
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

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }
}