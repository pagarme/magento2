<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Framework\Message\Factory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Pagarme\Pagarme\Helper\ProductHelper;
use Magento\Store\Model\StoreManagerInterface;

class PlanAction extends Action
{

    protected $resultPageFactory = false;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var Factory
     */
    protected $messageFactory;


    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        Factory $messageFactory,
        CollectionFactory $productCollectionFactory,
        JsonFactory $resultJsonFactory,
        ProductHelper $productHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        $this->productHelper = $productHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;

        $this->bootstrapDefaultStoreConfigurations();
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    }

    private function bootstrapDefaultStoreConfigurations()
    {
        $defaultStoreId = Magento2CoreSetup::getDefaultStoreId();
        $this->storeManager->setCurrentStore($defaultStoreId);

        Magento2CoreSetup::bootstrap();
    }
}
