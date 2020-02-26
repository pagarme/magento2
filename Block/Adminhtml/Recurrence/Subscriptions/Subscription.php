<?php

namespace MundiPagg\MundiPagg\Block\Adminhtml\Recurrence\Subscriptions;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class Subscription extends Template
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
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
        Registry $registry
    ) {
        parent::__construct($context, []);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry = $registry;
    }

    public function getProductId()
    {
        $productData = $this->coreRegistry->registry('subscription_data');
        if (empty($productData)) {
            return "";
        }
        $obj = json_decode($productData);
        return $obj->id;
    }

    public function getEditProduct()
    {
        $productData = $this->coreRegistry->registry('subscription_data');
        if (empty($productData)) {
            return "";
        }

        return $productData;
    }

    public function getRecurrenceType()
    {
        return $this->coreRegistry->registry('recurrence_type');
    }

    public function getBundleProducts()
    {
        $products = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(array('name', 'description'))
            ->addAttributeToFilter('type_id', 'simple');

        foreach ($collection as $product) {
            $products[$product->getEntityId()] = [
                'value' => $this->getFormattedName($product->getName()),
                'id' => $product->getEntityId()
            ];
        }

        return json_encode($products);
    }

    public function getFormattedName($name)
    {
        return str_replace("'", "", $name);
    }
    /**
     * @return array
     */
    public function getCicleSelectOption()
    {
        return [
            'interval_count' => range(1, 12),
            'interval_type' => [
                IntervalValueObject::INTERVAL_TYPE_MONTH => __('month'),
                IntervalValueObject::INTERVAL_TYPE_YEAR => __('year')
            ]
        ];
    }

    public function getSubscriptionDetails()
    {


        $this->_redirect('mundipagg_mundipagg/recurrenceproducts/index');
    }
}
