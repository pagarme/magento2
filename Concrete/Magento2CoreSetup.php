<?php

namespace Pagarme\Pagarme\Concrete;

use Magento\Framework\App\Config as Magento2StoreConfig;
use Magento\Config\Model\Config as Magento2ModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManager as MagentoStoreManager;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\ConfigInterface;
use Pagarme\Pagarme\Model\Installments\Config\ConfigInterface as InstallmentConfigInterface;
use Pagarme\Pagarme\Helper\ModuleHelper;
use Pagarme\Pagarme\Model\Enum\CreditCardBrandEnum;
use Pagarme\Pagarme\Concrete\Magento2DatabaseDecorator;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Concrete\Magento2PlatformInvoiceDecorator;
use Pagarme\Pagarme\Concrete\Magento2PlatformCreditmemoDecorator;
use Pagarme\Pagarme\Concrete\Magento2DataService;
use Pagarme\Pagarme\Concrete\Magento2PlatformPaymentMethodDecorator;
use Pagarme\Pagarme\Concrete\Magento2PlatformProductDecorator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website\Interceptor;
use stdClass;

final class Magento2CoreSetup extends AbstractModuleCoreSetup
{
    const MODULE_NAME = 'Pagarme_Pagarme';

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
            Magento2PlatformPaymentMethodDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS =>
            Magento2PlatformProductDecorator::class
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
        return '3470c63b-a233-4be0-9d2a-9ff56e349556';
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

    /**
     * @param ScopeConfigInterface|null $storeConfig
     */
    public function loadModuleConfigurationFromPlatform($storeConfig = null)
    {
        $objectManager = ObjectManager::getInstance();

        if ($storeConfig == null) {
            $storeConfig = $objectManager->get(Magento2StoreConfig::class);
        }

        $configData = new \stdClass();

        $storeId = self::getCurrentStoreId();

        if (!self::checkWebSiteExists($storeId)) {
            self::$moduleConfig = new Configuration();
            return;
        }

        self::fillWithGeneralConfig($configData, $storeConfig);
        self::fillWithPagarmeKeys($configData, $storeConfig);
        self::fillWithCardConfig($configData, $storeConfig);
        self::fillWithBoletoConfig($configData, $storeConfig);
        self::fillWithPixConfig($configData, $storeConfig);
        self::fillWithBoletoCreditCardConfig($configData, $storeConfig);
        self::fillWithTwoCreditCardsConfig($configData, $storeConfig);
        self::fillWithVoucherConfig($configData, $storeConfig);
        self::fillWithDebitConfig($configData, $storeConfig);
        self::fillWithAddressConfig($configData, $storeConfig);
        self::fillWithMultiBuyerConfig($configData, $storeConfig);
        self::fillWithRecurrenceConfig($configData, $storeConfig);
        self::fillWithHubConfig($configData, $storeConfig);

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
        self::$instance->setApiBaseUrl();
    }

    /**
     * @param int $webSiteId
     * @return bool
     */
    private static function checkWebSiteExists($webSiteId)
    {
        $objectManager = ObjectManager::getInstance();

        /**
         * @var StoreManagerInterface $storeManager
         */
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        /**
         * @var Interceptor[] $webSitesList
         */
        $webSitesList = $storeManager->getWebsites(true);

        foreach ($webSitesList as $website) {
            if ($website->getId() == $webSiteId) {
                return true;
            }
        }

        return false;
    }

    static private function fillWithVoucherConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'enabled' => 'active',
            'title' => 'title',
            'cardOperation' => 'payment_action',
            'cardStatementDescriptor' => 'soft_description',
            'saveCards' => 'enabled_saved_cards'
        ];

        $section = 'payment/pagarme_voucher/';

        $voucherObject = new \stdClass();

        $dataObj->voucherConfig = self::fillDataObj($storeConfig, $options, $voucherObject, $section);

        $operation = Configuration::CARD_OPERATION_AUTH_ONLY;
        if ($dataObj->voucherConfig->cardOperation === 'authorize_capture') {
            $operation  = Configuration::CARD_OPERATION_AUTH_AND_CAPTURE;
        }

        $dataObj->voucherConfig->cardOperation = $operation;
        $dataObj->voucherConfig->cardConfigs = self::getBrandConfig($storeConfig, $section);
    }

    static private function fillWithDebitConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'enabled' => 'active',
            'title' => 'title',
            'cardOperation' => 'payment_action',
            'cardStatementDescriptor' => 'soft_description',
            'saveCards' => 'enabled_saved_cards'
        ];

        $section = 'payment/pagarme_debit/';

        $debitObject = new \stdClass();

        $dataObj->debitConfig = self::fillDataObj($storeConfig, $options, $debitObject, $section);
        $dataObj->debitConfig->cardOperation = Configuration::CARD_OPERATION_AUTH_AND_CAPTURE;
        $dataObj->debitConfig->cardConfigs = self::getBrandConfig($storeConfig, $section);
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
        $section = 'payment/pagarme_creditcard/';

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

        $dataObj->cardConfigs = self::getBrandConfig($storeConfig, $section);

        return $dataObj;
    }

    private static function fillWithPixConfig(
        stdClass $configData,
        ScopeConfigInterface $storeConfig
    ) {
        $options = [
            'enabled' => 'active',
            'expirationQrCode' => 'expiration_qrcode',
            'bankType' => 'types',
            'title' => 'title'
        ];

        $section = 'payment/pagarme_pix/';

        $pixObject = new \stdClass();

        $configData->pixConfig = self::fillDataObj($storeConfig, $options, $pixObject, $section);
    }


    static private function fillWithBoletoConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'boletoEnabled' => 'active',
            'boletoInstructions' => 'instructions',
            'boletoDueDays' => 'expiration_days',
            'boletoBankCode' => 'types'
        ];
        $section = 'payment/pagarme_billet/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithBoletoCreditCardConfig(&$dataObj, $storeConfig)
    {
        $options = ['boletoCreditCardEnabled' => 'active'];
        $section = 'payment/pagarme_billet_creditcard/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithTwoCreditCardsConfig(&$dataObj, $storeConfig)
    {
        $options = ['twoCreditCardsEnabled' => 'active'];
        $section = 'payment/pagarme_two_creditcard/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithMultiBuyerConfig(&$dataObj, $storeConfig)
    {
        $options = ['multibuyer' => 'active'];
        $section = 'payment/pagarme_multibuyer/';
        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
    }

    static private function fillWithHubConfig(&$dataObj, $storeConfig)
    {
        $options = [
            'hubInstallId' => 'install_id',
            'hubEnvironment' => 'environment'
        ];

        $section = 'pagarme_pagarme/hub/';

        $dataObj = self::fillDataObj($storeConfig, $options, $dataObj, $section);
        if (!$dataObj->hubInstallId) {
            $dataObj->hubInstallId = null;
            $dataObj->hubEnvironment = null;
        }
    }

    static private function fillWithPagarmeKeys(&$dataObj, $storeConfig)
    {
        $options = [
            Configuration::KEY_SECRET => 'secret_key',
            Configuration::KEY_PUBLIC => 'public_key'
        ];

        if ($dataObj->testMode) {
            $options[Configuration::KEY_SECRET] .= '_test';
            $options[Configuration::KEY_PUBLIC] .= '_test';
        }

        $section = 'pagarme_pagarme/global/';

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
            'testMode' => 'test_mode',
            'sendMail' => 'sendmail',
            'createOrder' => 'create_order'
        ];

        $section = 'pagarme_pagarme/global/';

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
        $section = 'payment/pagarme_customer_address/';

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
        $scope = ScopeInterface::SCOPE_WEBSITES;

        $storeId = self::getCurrentStoreId();

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

    static private function getBrandConfig($storeConfig, $section)
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->get(Magento2ModelConfig::class);

        $scope = ScopeInterface::SCOPE_WEBSITES;
        $storeId = self::getCurrentStoreId();

        $brands = array_merge([''], explode(
            ',',
            $storeConfig->getValue($section .  'cctypes', $scope, $storeId)
        ));

        $cardConfigs = [];
        foreach ($brands as $brand) {
            $brand = "_" . strtolower($brand);
            $brandMethod = str_replace('_', '', $brand);
            $adapted = self::getBrandAdapter(strtoupper($brandMethod));
            if ($adapted !== false) {
                $brand = "_" . strtolower($adapted);
                $brandMethod = str_replace('_', '', $brand);
            }

            if ($brandMethod == '') {
                $brand = '';
                $brandMethod = 'nobrand';
            }

            $max = $storeConfig->getValue($section . 'installments_number' . $brand, $scope, $storeId);
            if (empty($max)) {
                $brand = '';
                $max = $storeConfig->getValue($section . 'installments_number' . $brand, $scope, $storeId);
            }

            $minValue =  $storeConfig->getValue($section . 'installment_min_amount' . $brand, $scope, $storeId);
            $initial =  $storeConfig->getValue($section . 'installments_interest_rate_initial' . $brand, $scope, $storeId);
            $incremental =  $storeConfig->getValue($section . 'installments_interest_rate_incremental' . $brand, $scope, $storeId);
            $maxWithout =  $storeConfig->getValue($section . 'installments_max_without_interest' . $brand, $scope, $storeId);

            $interestByBrand =  $storeConfig->getValue($section . 'installments_interest_by_issuer' . $brand, $scope, $storeId);
            if (empty($interestByBrand)) {
                $initial = 0;
                $incremental = 0;
                $maxWithout = $max;
            }

            $cardConfigs[] = new CardConfig(
                true,
                CardBrand::$brandMethod(),
                ($max !== null ? $max : 1),
                ($maxWithout !== null ? $maxWithout : 1),
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

    public static function getDefaultStoreViewCode()
    {
        $objectManager = ObjectManager::getInstance();
        $storeInterfaceName = '\Magento\Store\Model\StoreManagerInterface';
        $storeManager = $objectManager->get($storeInterfaceName);

        return $storeManager->getDefaultStoreView()->getCode();
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

            'showRecurrenceCurrencyWidget' => 'show_recurrence_currency_widget',

            'purchaseRecurrenceProductWithNormalProduct'
            => 'purchase_recurrence_product_with_normal_product',

            'conflictMessageRecurrenceProductWithNormalProduct'
            => 'conflict_recurrence_product_with_normal_product',

            'purchaseRecurrenceProductWithRecurrenceProduct'
            => 'purchase_recurrence_product_with_recurrence_product',

            'conflictMessageRecurrenceProductWithRecurrenceProduct'
            => 'conflict_recurrence_product_with_recurrence_product',

            'decreaseStock'
            => 'decrease_stock',
        ];

        $section = 'pagarme_pagarme/recurrence/';

        $recurrenceConfig = new \stdClass();
        $dataObj->recurrenceConfig = self::fillDataObj(
            $storeConfig,
            $options,
            $recurrenceConfig,
            $section
        );
    }
}
