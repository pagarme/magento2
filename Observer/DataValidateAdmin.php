<?php
namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;

class DataValidateAdmin implements ObserverInterface
{
    /**
     * Contains the config provider for Mundipagg
     *
     * @var \MundiPagg\MundiPagg\Model\MundiPaggConfigProvider
     */
    protected $configProviderMundipagg;

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
        MundiPaggConfigProvider $configProviderMundipagg,
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
        $this->configProviderMundipagg = $configProviderMundipagg;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $this->validateConfigMagento();
        
        return $this;
    }

    public function moduleIsEnable()
    {
        return $this->configProviderMundipagg->getModuleStatus();
    }

    protected function validateConfigMagento()
    {
        $disableModule = false;
        $disableMessage;
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/payment');

        if(!$this->configProviderMundipagg->validateSoftDescription()){
            $disableModule = true;
            $disableMessage[] = __("Error to save MundiPagg Soft Description Credit Card, size too big max 22 character." , 
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
}