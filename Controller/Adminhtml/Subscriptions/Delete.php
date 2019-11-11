<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Factory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\ProductsSubscriptionFactory;

class Delete extends Action
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
        ProductsSubscriptionFactory $productsSubscriptionFactory,
        Factory $messageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->productsSubscriptionFactory = $productsSubscriptionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
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
                $message = $this->messageFactory->create('error', __('Product subscription not exist.'));
                $this->messageManager->addErrorMessage($message);
                $this->_redirect('mundipagg_mundipagg/subscriptions/index');
                return;
            }

        }

        $productSubscriptionService->delete($productId);

        $message = $this->messageFactory->create('success', _("Product subscription deleted."));
        $this->messageManager->addMessage($message);

        $this->_redirect('mundipagg_mundipagg/subscriptions/index');
        return;
    }
}
