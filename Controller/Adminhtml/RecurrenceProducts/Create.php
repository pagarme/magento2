<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\RecurrenceProducts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\ProductsSubscriptionFactory;

class Create extends Action
{
    protected $resultPageFactory;

    /**
     * @var ProductsSubscriptionFactory
     */
    protected $productsSubscriptionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        ProductsSubscriptionFactory $productsSubscriptionFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->productsSubscriptionFactory = $productsSubscriptionFactory;
        $this->coreRegistry = $coreRegistry;
        Magento2CoreSetup::bootstrap();

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        if ($productId) {

            $productSubscriptionService = new ProductSubscriptionService();
            $productData = $productSubscriptionService->findById($productId);

            if (!$productData || !$productData->getId()) {
                $this->messageManager->addError(__('Product subscription not exist.'));
                $this->_redirect('pagarme_pagarme/recurrenceproducts/index');
                return;
            }

            $this->coreRegistry->register('subscription_data', json_encode($productData));
        }
        $this->coreRegistry->register('recurrence_type', ProductSubscription::RECURRENCE_TYPE);

        $title = $productId ? __('Edit Recurrence Product') : __('Create Recurrence Product');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
