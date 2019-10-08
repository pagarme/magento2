<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use MundiPagg\MundiPagg\Helper\ProductHelper;

class SearchProduct extends Action
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
    private $productHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $productCollectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $productCollectionFactory,
        JsonFactory $resultJsonFactory,
        ProductHelper $productHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('productId');

        $objectManager = ObjectManager::getInstance();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store_id = $storeManager->getStore()->getId();

        $product = $objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
        $options = $objectManager->get('Magento\Bundle\Model\Option')
            ->getResourceCollection()
            ->setProductIdFilter($productId)
            ->setPositionOrder();

        $options->joinValues($store_id);
        $typeInstance = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);

        $bundleProducts = [];
        foreach ($selections as $bundle) {
            $bundleProducts[] = [
                "code" => $bundle->getEntityId(),
                "name" => $bundle->getName(),
                "image" => $this->productHelper->getProductImage($bundle->getEntityId()),
                "price" => $bundle->getPrice()
            ];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($bundleProducts);
    }

}