<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Plans;


use Pagarme\Pagarme\Controller\Adminhtml\Plans\PlanAction;
use Pagarme\Core\Recurrence\Services\PlanService;

class Delete extends PlanAction
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        if ($productId) {

            $planService = new PlanService();
            $productData = $planService->findById($productId);

            if (!$productData || !$productData->getId()) {
                $message = $this->messageFactory->create('error', __('Plan not exist.'));
                $this->messageManager->addErrorMessage($message);
                $this->_redirect('pagarme_pagarme/plans/index');
                return;
            }
        }

        $planService->delete($productId);

        $message = $this->messageFactory->create('success', _("Plan deleted."));
        $this->messageManager->addMessage($message);

        $this->_redirect('pagarme_pagarme/plans/index');
        return;
    }
}
