<?php

namespace MundiPagg\MundiPagg\Block\Adminhtml\Recurrence\Subscriptions;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository;
use Mundipagg\Core\Recurrence\Services\SubscriptionService;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use MundiPagg\MundiPagg\Ui\Component\Column\Invoices\Actions;
use MundiPagg\MundiPagg\Ui\Component\Recurrence\Column\TotalCyclesByProduct;

class Subscription extends Template
{
    const INACTIVE_STATUS = 'INACTIVE';
    const CANCELED_STATUS = 'CANCELED';
    const URL_PATH_DELETE = 'mundipagg_mundipagg/invoices/delete';

    private $objectManager;

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
        $this->objectManager = ObjectManager::getInstance();

        Magento2CoreSetup::bootstrap();
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
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        if ($subscriptionId) {
            $subscriptionId = new SubscriptionId($subscriptionId);
            $subscriptionService = new SubscriptionService();

            return $subscriptionService->getSavedSubscription($subscriptionId);
        }
    }

    public function getTotalCycles($orderId)
    {
        $recurrenceProductHelper = new RecurrenceProductHelper();
        $products = $this->getProducts($orderId);

        $cycles = [];

        foreach ($products as $product) {
            $cycles[] =
                $recurrenceProductHelper
                    ->getSelectedRepetitionByProduct($product);
        }

        return $recurrenceProductHelper->returnHighestCycle($cycles);
    }

    public function getDisabledStatusName()
    {
        return self::INACTIVE_STATUS;
    }

    public function getProducts($orderId)
    {
        $magentoOrder = $this->objectManager
                ->get('Magento\Sales\Model\Order')
                ->loadByIncrementId($orderId);
        return $magentoOrder->getAllItems();
    }

    public function getProductOptions($product)
    {
        if (method_exists($product, 'getProductOptions')) {
            $productOptions = $product->getProductOptions();
            if (empty($productOptions['options'])) {
                return;
            }
            if (empty($productOptions['options'][0])) {
                return;
            }

            return $productOptions['options'][0]['value'];
        }
    }

    public function getCancelUrl($invoiceId)
    {
        $url = $this->_urlBuilder->getUrl(self::URL_PATH_DELETE);
        $url .= "?id={$invoiceId}";

        return $url;
    }

    public function centsToFloat($amountInCents)
    {
        $moneyService = new MoneyService();
        return number_format($moneyService->centsToFloat($amountInCents), '2', ',', '.');
    }

    public function getProductCycles($product)
    {
        $recurrenceProductHelper = new RecurrenceProductHelper();
        return $recurrenceProductHelper->getSelectedRepetitionByProduct($product);
    }
}
