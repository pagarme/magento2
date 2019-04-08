<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\Config as Magento2StoreConfig;
use Magento\Config\Model\Config as Magento2ModelConfig;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManager as MagentoStoreManager;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Factories\ConfigurationFactory;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\ConfigInterface;
use MundiPagg\MundiPagg\Model\Installments\Config\ConfigInterface as InstallmentConfigInterface;
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

    static protected function setPlatformVersion()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $objectManager->get(ProductMetadataInterface::class);
        $version = $productMetadata->getName() . ' ';
        $version .= $productMetadata->getEdition() . ' ';
        $version .= $productMetadata->getVersion();

        self::$platformVersion = $version;
    }

    static protected function setLogPath()
    {
        $objectManager = ObjectManager::getInstance();

        $directoryConfig = $objectManager->get(DirectoryList::class);

        self::$logPath = [
            $directoryConfig->getPath('log'),
            $directoryConfig->getPath('var') . DIRECTORY_SEPARATOR . 'report'
        ];
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

    public static function loadModuleConfigurationFromPlatform()
    {
        $storeId = self::getCurrentStoreId();
        $scope = ScopeInterface::SCOPE_STORE;

        $objectManager = ObjectManager::getInstance();
        $platformBaseConfig = $objectManager->get(Config::class);
        $storeConfig = $objectManager->get(Magento2StoreConfig::class);

        $configData = new \stdClass;

        $configData = self::fillWithCardConfigs($configData, $storeConfig);

        /**
         * @todo Refact billet, billet_creditcard and other sections
         */

        $configData->boletoEnabled = $storeConfig->getValue('payment/mundipagg_billet/active') === '1';

        $configData->boletoCreditCardEnabled = $storeConfig->getValue('payment/mundipagg_billet_creditcard/active') === '1';
        $configData->twoCreditCardsEnabled = $storeConfig->getValue('payment/mundipagg_two_creditcard/active') === '1';

        $configData->hubInstallId = null;
        $configData->enabled =
            $storeConfig->getValue('mundipagg/general/is_active') === '1' &&
            $storeConfig->getValue('mundipagg_mundipagg/global/active') === '1';

        $configData->testMode = $platformBaseConfig->getTestMode();
        $configData->keys = [
            Configuration::KEY_PUBLIC => $platformBaseConfig->getPublicKey(),
            Configuration::KEY_SECRET => $platformBaseConfig->getSecretKey(),
        ];

        $configData->addressAttributes = new \stdClass();
        $configData->addressAttributes->street =
            $storeConfig->getValue('payment/mundipagg_customer_address/street_attribute');
        $configData->addressAttributes->number =
            $storeConfig->getValue('payment/mundipagg_customer_address/number_attribute');
        $configData->addressAttributes->neighborhood =
            $storeConfig->getValue('payment/mundipagg_customer_address/district_attribute');
        $configData->addressAttributes->complement =
            $storeConfig->getValue('payment/mundipagg_customer_address/complement_attribute');

        $configData->boletoInstructions =
            $storeConfig->getValue('payment/mundipagg_billet/instructions');

        $configData->multiBuyer =
            $storeConfig->getValue(
                InstallmentConfigInterface::PATH_MULTI_BUYER_ACTIVE
            ) === '1';

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }

    static private function fillWithCardConfigs($dataObj, $storeConfig)
    {
        $moneyService = new MoneyService();

        $options = [
            'creditCardEnabled' => 'active',
            'installmentsEnabled' => 'installments_active',
            'cardOperation' => 'payment_action',
            'cardStatementDescriptor' => 'soft_description',
            'isAntifraudEnabled' => 'antifraud_active',
            'antifraudMinAmount' => 'antifraud_min_amount'
        ];
        $cardSection = 'payment/mundipagg_creditcard/';

        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $cardSection);

        if ($dataObj->cardOperation === 'authorize_capture') {
            $dataObj->cardOperation = Configuration::CARD_OPERATION_AUTH_AND_CAPTURE;
        } else {
            $dataObj->cardOperation = Configuration::CARD_OPERATION_AUTH_ONLY;
        }

        $dataObj->antifraudMinAmount =
            $moneyService->floatToCents($dataObj->antifraudMinAmount * 1);

        $dataObj->saveCards =
            $storeConfig->getValue(ConfigInterface::PATH_ENABLED_SAVED_CARDS) === '1';

        $dataObj->cardConfigs = self::getBrandConfigs($storeConfig);

        return $dataObj;
    }

    static private function fillDataObj($storeConfig, $options, $dataObj, $section)
    {
        $storeId = self::getCurrentStoreId();
        $scope = ScopeInterface::SCOPE_STORE;

        foreach ($options as $key => $option) {
            $value = $storeConfig->getValue($section . $option, $scope, $storeId);

            if (!$value) {
                $value = false;
            }

            if ($value === '1') {
                $value = true;
            }

            $dataObj->$key = $value;
        }

        return $dataObj;
    }

    static private function getBrandConfigs($storeConfig)
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

    public static function getCurrentStoreId()
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->get(Magento2ModelConfig::class);

        if (!$config->getStore()) {
            return 1;
        }

        return $config->getStore();
    }

    public static function getDefaultStoreId()
    {
        $objectManager = ObjectManager::getInstance();
        $storeInterfaceName = '\Magento\Store\Model\StoreManagerInterface';
        $storeManager = $objectManager->get($storeInterfaceName);

        $stores = $storeManager->getStores();
        $store = current($stores);

        return $store->getStoreId();
    }
}