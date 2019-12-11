<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\Config as Magento2StoreConfig;
use Magento\Config\Model\Config as Magento2ModelConfig;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
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

    protected function setModuleVersion()
    {
        $objectManager = ObjectManager::getInstance();
        $moduleHelper = $objectManager->get(ModuleHelper::class);

        self::$moduleVersion = $moduleHelper->getVersion(self::MODULE_NAME);
    }

    protected function setPlatformVersion()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $objectManager->get(ProductMetadataInterface::class);
        $version = $productMetadata->getName() . ' ';
        $version .= $productMetadata->getEdition() . ' ';
        $version .= $productMetadata->getVersion();

        self::$platformVersion = $version;
    }

    protected function setLogPath()
    {
        $objectManager = ObjectManager::getInstance();

        $directoryConfig = $objectManager->get(DirectoryList::class);

        self::$logPath = [
            $directoryConfig->getPath('log'),
            $directoryConfig->getPath('var') . DIRECTORY_SEPARATOR . 'report'
        ];
    }

    protected function setConfig()
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
                Magento2DataService::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_PAYMENT_METHOD_DECORATOR_CLASS =>
                Magento2PlatformPaymentMethodDecorator::class
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

    public function _getDashboardLanguage()
    {
        $objectManager = ObjectManager::getInstance();
        $resolver = $objectManager->get('Magento\Framework\Locale\Resolver');

        return $resolver->getLocale();
    }

    public function _getStoreLanguage()
    {
        /**
         * @todo verify if this work as expected in the store screens.
         *       On dashboard, this will return null.
         */
        $objectManager = ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Api\Data\StoreInterface');

        return $store->getLocaleCode();
    }

    public function loadModuleConfigurationFromPlatform()
    {
        $storeId = self::getCurrentStoreId();
        $scope = ScopeInterface::SCOPE_STORE;

        $objectManager = ObjectManager::getInstance();
        $storeConfig = $objectManager->get(Magento2StoreConfig::class);
        $configData = new \stdClass;

        self::fillWithGeneralConfig($configData, $storeConfig);
        self::fillWithMundipaggKeys($configData, $storeConfig);
        self::fillWithCardConfig($configData, $storeConfig);
        self::fillWithBoletoConfig($configData, $storeConfig);
        self::fillWithBoletoCreditCardConfig($configData, $storeConfig);
        self::fillWithTwoCreditCardsConfig($configData, $storeConfig);
        self::fillWithAddressConfig($configData, $storeConfig);
        self::fillWithMultiBuyerConfig($configData, $storeConfig);
        self::fillWithRecurrenceConfig($configData, $storeConfig);
        $configData->hubInstallId = null;

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }

    static private function fillWithCardConfig(&$dataObj, $storeConfig)
    {
        $moneyService = new MoneyService();

        $options = [
            'creditCardEnabled' => 'active',
            'installmentsEnabled' => 'installments_active',
            'cardOperation' => 'payment_action',
            'cardStatementDescriptor' => 'soft_description',
            'antifraudEnabled' => 'antifraud_active',
            'antifraudMinAmount' => 'antifraud_min_amount',
            'saveCards' => 'enabled_saved_cards',
            'installmentsDefaultConfig' => 'installments_type'
        ];
        $section = 'payment/mundipagg_creditcard/';

        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);

        if ($dataObj->cardOperation === 'authorize_capture') {
            $dataObj->cardOperation = Configuration::CARD_OPERATION_AUTH_AND_CAPTURE;
        } else {
            $dataObj->cardOperation = Configuration::CARD_OPERATION_AUTH_ONLY;
        }

        $dataObj->antifraudMinAmount =
            $moneyService->floatToCents(
                $dataObj->antifraudMinAmount * 1
            );

        $dataObj->cardConfigs = self::getBrandConfig($storeConfig);

        return $dataObj;
    }

    static private function fillWithBoletoConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'boletoEnabled' => 'active',
            'boletoInstructions' => 'instructions',
            'boletoDueDays' => 'expiration_days',
            'boletoBankCode' => 'types'
        ];
        $section = 'payment/mundipagg_billet/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithBoletoCreditCardConfig(&$dataObj, $storeConfig)
    {
        $options = ['boletoCreditCardEnabled' => 'active'];
        $section = 'payment/mundipagg_billet_creditcard/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithTwoCreditCardsConfig(&$dataObj, $storeConfig)
    {
        $options = ['twoCreditCardsEnabled' => 'active'];
        $section = 'payment/mundipagg_two_creditcard/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithMultiBuyerConfig(&$dataObj, $storeConfig)
    {
        $options = ['multibuyer' => 'active'];
        $section = 'payment/mundipagg_multibuyer/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithMundipaggKeys(&$dataObj, $storeConfig)
    {
        $options = [
            Configuration::KEY_SECRET => 'secret_key',
            Configuration::KEY_PUBLIC => 'public_key'
        ];

        if ($dataObj->testMode) {
            $options[Configuration::KEY_SECRET] .= '_test';
            $options[Configuration::KEY_PUBLIC] .= '_test';
        }

        $section = 'mundipagg_mundipagg/global/';

        $keys = new \stdClass;

        $dataObj->keys =
            self::fillDataObj(
                $storeConfig,
                $options,
                $keys,
                $section
            );
    }

    static private function fillWithGeneralConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'enabled' => 'active',
            'testMode' => 'test_mode'
        ];

        $section = 'mundipagg_mundipagg/global/';

        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithAddressConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'street' => 'street_attribute',
            'number' => 'number_attribute',
            'neighborhood' => 'district_attribute',
            'complement' => 'complement_attribute',
        ];
        $section = 'payment/mundipagg_customer_address/';

        $addressAttributes = new \stdClass();
        $dataObj->addressAttributes =
            self::fillDataObj(
                $storeConfig,
                $options,
                $addressAttributes,
                $section
            );
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

    static private function getBrandConfig($storeConfig)
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

            $max = $storeConfig->getValue('payment/mundipagg_creditcard/installments_number' . $brand);
            if (empty($max)) {
                $brand = '';
                $max = $storeConfig->getValue('payment/mundipagg_creditcard/installments_number' . $brand);
            }

            $minValue =  $storeConfig->getValue('payment/mundipagg_creditcard/installment_min_amount' . $brand);
            $initial =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_rate_initial' . $brand);
            $incremental =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_rate_incremental'. $brand);
            $maxWithout =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_max_without_interest' . $brand);

            $interestByBrand =  $storeConfig->getValue('payment/mundipagg_creditcard/installments_interest_by_issuer' . $brand);
            if (empty($interestByBrand)) {
                $initial = 0;
                $incremental = 0;
                $maxWithout = $max;
            }

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

    protected function _formatToCurrency($price)
    {
        $objectManager = ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');

        return $priceHelper->currency($price, true, false);
    }

    public static function getCurrentStoreId()
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->get(Magento2ModelConfig::class);

        $storeInterfaceName = '\Magento\Store\Model\StoreManagerInterface';
        $storeManager = $objectManager->get($storeInterfaceName);

        $store = $storeManager->getWebsite()->getId();

        if ($config->getScope() == 'websites') {
            $store = $config->getScopeId();
        }

        if ($config->getScope() == 'stores') {
            $store = $storeManager
                ->getStore($config->getScopeId())
                ->getWebsite()
                ->getId();
        }

        if ($config->getScope() == 'default') {
            $store = $storeManager->getDefaultStoreView()->getStoreId();
        }

        return $store;
    }

    public static function getDefaultStoreId()
    {
        $objectManager = ObjectManager::getInstance();
        $storeInterfaceName = '\Magento\Store\Model\StoreManagerInterface';
        $storeManager = $objectManager->get($storeInterfaceName);

        $defaultStoreView = $storeManager->getDefaultStoreView();

        return $defaultStoreView->getStoreId();
    }

    /**
     * @since 1.7.1
     *
     * @return \DateTimeZone
     */
    protected function getPlatformStoreTimezone()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var TimezoneInterface $timezone */
        $timezone = $objectManager->create(
            TimezoneInterface::class
        );
        $timezoneString = $timezone->getConfigTimezone(
            ScopeInterface::SCOPE_STORE
        );
        $dateTimeZone = new \DateTimeZone($timezoneString);

        return $dateTimeZone;
    }

    static private function fillWithRecurrenceConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'enabled' => 'active',
            'checkoutConflictMessage' => 'conflict_message'
        ];

        $section = 'mundipagg_mundipagg/recurrence/';

        $recurrenceConfig = new \stdClass();
        $dataObj->recurrenceConfig = self::fillDataObj(
            $storeConfig,
            $options,
            $recurrenceConfig,
            $section
        );
    }
}