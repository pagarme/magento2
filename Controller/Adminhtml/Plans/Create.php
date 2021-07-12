<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Plans;


use Pagarme\Pagarme\Controller\Adminhtml\Plans\PlanAction;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Services\PlanService;

class Create extends PlanAction
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $planId = (int) $this->getRequest()->getParam('id');
        if ($planId) {
            //@todo this should be a product plan core object
            $planService = new PlanService();
            $planData = $planService->findById($planId);

            if (!$planData || !$planData->getId()) {
                $this->messageManager->addError(__('Product plan not exist.'));
                $this->_redirect('pagarme_pagarme/plans/index');
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
