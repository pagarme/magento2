<?php

namespace MundiPagg\MundiPagg\Block\Adminhtml\Recurrence\Plans;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MundiPagg\MundiPagg\Helper\ProductHelper;

class Plan extends Template
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Link constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Registry $registry
     * @param ProductHelper $productHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Registry $registry,
        ProductHelper $productHelper
    ){
        parent::__construct($context, []);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry = $registry;
        $this->productHelper = $productHelper;
    }

    public function getEditProduct()
    {
        $productData = $this->coreRegistry->registry('product_data');
        if (empty($productData)) {
            return "";
        }

        return json_encode($productData->toArray());
    }

    public function getBundleProducts()
    {
        $products = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(array('name', 'description'))
            ->addAttributeToFilter('type_id', 'bundle');

        foreach ($collection as $product) {
            $products[$product->getEntityId()] = [
                'value' => $product->getName(),
                'id' => $product->getEntityId(),
                'image' => $this->productHelper->getProductImage($product->getEntityId())
            ];
        }

        return json_encode($products);
    }


}