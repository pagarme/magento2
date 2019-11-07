<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MundiPagg\MundiPagg\Model\ProductsPlanFactory;

class Create extends Action
{
    protected $resultPageFactory = false;
    /**
     * @var ProductsPlanFactory
     */
    private $productsPlanFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        ProductsPlanFactory $productsPlanFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->productsPlanFactory = $productsPlanFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        if($productId) {
            //@todo this should be a product plan core object
            $productData = $this->productsPlanFactory->create()->load($productId);
            if(!$productData->getId()) {
                // @todo Add Error
                // $this->messageManager->addError(__('row data no longer exist.'));

                $this->_redirect('mundipagg_mundipagg/plans/index');
                return;
            }
            $this->coreRegistry->register('product_data', $productData);
            $this->coreRegistry->register('recurrence_type', $productData->getRecurrenceType());
        }

        $title = $productId ? __('Edit Plan') : __('Create Plan');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
