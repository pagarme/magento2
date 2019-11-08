<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Services\PlanService;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
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
    protected $productHelper;

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
        Magento2CoreSetup::bootstrap();
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('productId');
        $recurrenceType = $this->getRequest()->getParam('recurrenceType');
        $recurrenceProductId = $this->getRequest()->getParam('recurrenceProductId');

        $objectManager = ObjectManager::getInstance();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store_id = $storeManager->getStore()->getId();

        $productBundle = $objectManager->get('\Magento\Catalog\Model\Product')->load($productId);

        if (empty($productBundle) || $productBundle->getHasOptions() == 0) {
            return;
        }

        $options = $objectManager->get('Magento\Bundle\Model\Option')
            ->getResourceCollection()
            ->setProductIdFilter($productId)
            ->setPositionOrder();

        $options->joinValues($store_id);
        $typeInstance = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($productBundle), $productBundle);
        $moneyService = new MoneyService();

        $bundleProducts = [];
        foreach ($selections as $bundle) {
            $product = [
                "code" => $bundle->getEntityId(),
                "name" => $bundle->getName(),
                "image" => $this->productHelper->getProductImage($bundle->getEntityId()),
                "price" => $moneyService->floatToCents(
                    $bundle->getSelectionPriceValue()
                ),
            ];

            $subProductRecurrence = $this->getProductRecurrence(
                $bundle->getEntityId(),
                $recurrenceProductId,
                $recurrenceType
            );


            if ($subProductRecurrence !== null) {
                $product['cycles'] = $subProductRecurrence->getCycles();
                $product['quantity'] = $subProductRecurrence->getQuantity();
                $product['id'] = $subProductRecurrence->getId();
            }

            $bundleProducts[] = $product;
        }

        $bundleProducts['productBundle'] = [
            'id' => $productBundle->getEntityId(),
            'name' => $productBundle->getName(),
            'description' => $productBundle->getDescription()
        ];

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($bundleProducts);
    }

    public function getProductRecurrence($productId, $recurrenceProductId, $recurrenceType)
    {
        if (empty($recurrenceProductId)) {
            return null;
        }

        $recurrenceService = $this->getRecurrenceService($recurrenceType);
        $recurrenceEntity = $recurrenceService->findById($recurrenceProductId);

        foreach ($recurrenceEntity->getItems() as $item) {
            if ($item->getProductId() == $productId) {
                return $item;
            }
        }
    }

    public function getRecurrenceService($recurrenceType)
    {
        if ($recurrenceType == Plan::RECURRENCE_TYPE) {
            return new PlanService();
        }
        return new ProductSubscriptionService();
    }
}