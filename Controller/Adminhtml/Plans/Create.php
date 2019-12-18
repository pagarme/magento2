<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Plans;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Repositories\PlanRepository;
use Mundipagg\Core\Recurrence\Services\PlanService;

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
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $planId = (int) $this->getRequest()->getParam('id');
        if($planId) {
            //@todo this should be a product plan core object
            $planService = new PlanService();
            $planData = $planService->findById($planId);

            if (!$planData || !$planData->getId()) {
                $this->messageManager->addError(__('Product plan not exist.'));
                $this->_redirect('mundipagg_mundipagg/plans/index');
                return;
            }
            $this->coreRegistry->register('product_data', json_encode($planData));
        }

        $this->coreRegistry->register('recurrence_type', Plan::RECURRENCE_TYPE);
        $title = $planId ? __('Edit Plan') : __('Create Plan');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
