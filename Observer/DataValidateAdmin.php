<?php
namespace Pagarme\Pagarme\Observer;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class DataValidateAdmin implements ObserverInterface
{
    /**
     * Contains the config provider for Pagar.me
     *
     * @var \Pagarme\Pagarme\Model\PagarmeConfigProvider
     */
    protected $configProviderPagarme;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $cacheFrontendPool;

    public function __construct(
        PagarmeConfigProvider $configProviderPagarme,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ResponseFactory $responseFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    )
    {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->responseFactory = $responseFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->configProviderPagarme = $configProviderPagarme;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->updateModuleConfiguration();

        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $this->validateConfigMagento();

        if (!$this->isGatewayIntegrationType()) {
            $this->configProviderPagarme->disableVoucher();
            $this->configProviderPagarme->disableDebit();
            $this->configProviderPagarme->disableRecurrence();
            $this->configProviderPagarme->disableSavedCard();
            $this->configProviderPagarme->disableAntifraud();

            ObjectManager::getInstance()->get(Cache::class)
                ->clean(Config::CACHE_TAG);
        }

        return $this;
    }

    protected function initializeModule()
    {
        Magento2CoreSetup::bootstrap();
    }

    private function updateModuleConfiguration()
    {
        $this->initializeModule();

        $moduleConfig = AbstractModuleCoreSetup::getModuleConfiguration();
        $configRepository = new ConfigurationRepository();

        $outdatedConfiguration =
            $this->getOutDatedConfiguration(
                $moduleConfig,
                $configRepository
            );

        if ($outdatedConfiguration !== null) {
            $moduleConfig->setId($outdatedConfiguration->getId());
        }

        $configRepository->save($moduleConfig);

        AbstractModuleCoreSetup::setModuleConfiguration($moduleConfig);
    }

    public function moduleIsEnable()
    {
        return $this->configProviderPagarme->getModuleStatus();
    }

    public function isGatewayIntegrationType()
    {
        return $this->configProviderPagarme->isGatewayIntegrationType();
    }

    protected function validateConfigMagento()
    {
        $disableModule = false;
        $disableMessage;
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/payment');

        if (!$this->configProviderPagarme->validateMaxInstallment()) {
            $disableModule = true;
            $disableMessage[] = __("Error to save Pagar.me Max Installments, size too big." ,
                $url
            );
        }

        if (!$this->configProviderPagarme->validateSoftDescription()) {
            $disableModule = true;
            $disableMessage[] = __("Error to save Pagar.me Soft Descriptor Credit Card, size too big.",
                $url
            );
        }

        if ($disableModule) {
            $this->disableModule($disableMessage, $url);
        }

        return $this;
    }

    protected function disableModule($disableMessage, $url)
    {
        foreach ($disableMessage as $message) {
            $this->messageManager->addError($message);
        }

        $this->cleanCache();

        $this->responseFactory->create()
                ->setRedirect($url)
                ->sendResponse();
        exit(0);
    }

    protected function cleanCache()
    {
        $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        return $this;
    }

    protected function getOutDatedConfiguration($moduleConfig, $configRepository)
    {
        $storeId = Magento2CoreSetup::getCurrentStoreId();
        $moduleConfig->setStoreId($storeId);

        return $configRepository->findByStore($storeId);
    }
}
