<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Recurrence\Subscriptions;

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
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Services\SubscriptionService;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Pagarme\Pagarme\Ui\Component\Column\Invoices\Actions;
use Pagarme\Pagarme\Ui\Component\Recurrence\Column\TotalCyclesByProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Interceptor as ProductInterceptor;

class Subscription extends Template
{
    const INACTIVE_STATUS = 'INACTIVE';
    const CANCELED_STATUS = 'canceled';
    const URL_PATH_DELETE = 'pagarme_pagarme/invoices/delete';
    const URL_PATH_CANCEL_SUBSCRIPTION = 'pagarme_pagarme/subscriptions/delete/id';

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

        /**
         * @var  ProductCollection|ProductInterceptor[] $collection
         */
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(array('name', 'description'))
            ->addAttributeToFilter('type_id', ['simple', 'virtual', 'downloadable']);

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
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $name)) {
            $name = preg_replace('/[^a-zA-Z0-9 ]+/i', '', $name);
        }
        return $name;
    }
    /**
     * @return array
     */
    public function getCicleSelectOption()
    {
        return [
            'interval_count' => range(1, 12),
            'interval_type' => [
                IntervalValueObject::INTERVAL_TYPE_WEEK => __('week'),
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
        return $recurrenceProductHelper->getHighestProductCycle($orderId);
    }

    public function getDisabledStatusName()
    {
        return self::CANCELED_STATUS;
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

    public function getCancelInvoiceUrl($invoiceId)
    {
        $url = $this->_urlBuilder->getUrl(self::URL_PATH_DELETE);
        $url .= "?id={$invoiceId}";

        return $url;
    }

    public function getCancelSubscriptionUrl($subscriptionId)
    {
        $url = self::URL_PATH_CANCEL_SUBSCRIPTION .
            "/{$subscriptionId}";

        return $this->_urlBuilder->getUrl($url);
    }
}
