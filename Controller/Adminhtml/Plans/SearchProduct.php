<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Services\PlanService;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductHelper;

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
        $recurrenceProductId =
            $this->getRequest()->getParam('recurrenceProductId');

        $objectManager = ObjectManager::getInstance();

        $product = $objectManager->get('\Magento\Catalog\Model\Product')
            ->load($productId);

        if (empty($product) ) {
            return;
        }

        if ($recurrenceType === Plan::RECURRENCE_TYPE) {
            $bundleProducts = $this->getProductsPlanArray(
                $product,
                $recurrenceProductId,
                $recurrenceType
            );
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($bundleProducts);
        }

        $products = $this->getProductSubscriptionArray(
            $product,
            $recurrenceProductId,
            $recurrenceType
        );

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($products);
    }

    public function getProductsPlanArray(
        $productBundle,
        $recurrenceProductId,
        $recurrenceType
    ) {
        $objectManager = ObjectManager::getInstance();
        if ($productBundle->getHasOptions() == 0) {
            return;
        }

        $typeInstance = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $selections = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($productBundle),
            $productBundle
        );
        $moneyService = new MoneyService();

        $bundleProducts = [];
        foreach ($selections as $bundle) {
            $product = [
                "code" => $bundle->getEntityId(),
                "name" => $this->getFormattedName($bundle->getName()),
                "image" => $this->productHelper->getProductImage(
                    $bundle->getEntityId()
                ),
                "price" => $moneyService->floatToCents(
                    $bundle->getSelectionPriceValue()
                ),
                "quantity" => (int) $bundle->getSelectionQty()
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
                $product['pagarme_id'] = $subProductRecurrence->getMundipaggId();
            }

            $bundleProducts[] = $product;
        }

        $bundleProducts['productBundle'] = [
            'id' => $productBundle->getEntityId(),
            'name' => $productBundle->getName(),
            'description' => $productBundle->getDescription()
        ];

        return $bundleProducts;
    }

    public function getFormattedName($name)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $name)) {
            $name = preg_replace('/[^a-zA-Z0-9 ]+/i', '', $name);
        }
        return $name;
    }

    public function getProductSubscriptionArray(
        $simpleProduct,
        $recurrenceProductId,
        $recurrenceType
    ) {
        $moneyService = new MoneyService();

        $product = [
            "code" => $simpleProduct->getEntityId(),
            "name" => $simpleProduct->getName(),
            "image" => $this->productHelper->getProductImage(
                $simpleProduct->getEntityId()
            ),
            "price" => $moneyService->floatToCents(
                $simpleProduct->getPrice()
            ),
        ];

        $subProductRecurrence = $this->getProductRecurrence(
            $simpleProduct->getEntityId(),
            $recurrenceProductId,
            $recurrenceType
        );

        if ($subProductRecurrence !== null) {
            $product['id'] = $subProductRecurrence->getId();
        }

        $products[] = $product;

        $products['productBundle'] = [
            'id' => $simpleProduct->getEntityId(),
            'name' => $simpleProduct->getName(),
            'description' => $simpleProduct->getDescription()
        ];

        return $products;
    }

    public function getProductRecurrence(
        $productId,
        $recurrenceProductId,
        $recurrenceType
    ) {
        if (empty($recurrenceProductId)) {
            return null;
        }

        $recurrenceService = $this->getRecurrenceService($recurrenceType);
        $recurrenceEntity = $recurrenceService->findById($recurrenceProductId);

        if ($recurrenceEntity->getRecurrenceType() == ProductSubscription::RECURRENCE_TYPE) {
            return $recurrenceEntity;
        }

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
