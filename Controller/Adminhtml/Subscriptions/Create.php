<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MundiPagg\MundiPagg\Model\ProductsSubscriptionFactory;

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
            $productData = $this->productsSubscriptionFactory->create()->load($productId);

            if (!$productData->getId()) {
                $this->messageManager->addError(__('row data no longer exist.'));
                $this->_redirect('mundipagg_mundipagg/subscriptions/index');
                return;
            }

            $this->coreRegistry->register('subscription_data', $productData);
        }

        $title = $productId ? __('Edit Subscription') : __('Create Subscription');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
