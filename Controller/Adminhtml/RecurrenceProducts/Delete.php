<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\RecurrenceProducts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductSubscriptionHelper;
use Pagarme\Pagarme\Model\ProductsSubscriptionFactory;

class Delete extends Action
{
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Factory
     */
    protected $messageFactory;

    /**
     * @var ProductSubscriptionHelper
     */
    protected $productSubscriptionHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param Factory $messageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        Factory $messageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        Magento2CoreSetup::bootstrap();

        $this->productSubscriptionHelper = new ProductSubscriptionHelper();

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResponseInterface
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
                return $this->_redirect('pagarme_pagarme/recurrenceproducts/index');
            }
        }

        $this->productSubscriptionHelper->deleteRecurrenceCustomOption($productData);

        $productSubscriptionService->delete($productId);

        $message = $this->messageFactory->create('success', __("Product subscription deleted."));
        $this->messageManager->addMessage($message);

        return $this->_redirect('pagarme_pagarme/recurrenceproducts/index');
    }
}
