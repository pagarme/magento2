<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Plans;

use Pagarme\Pagarme\Controller\Adminhtml\Plans\PlanAction;

class Index extends PlanAction
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Plans"));

        return $resultPage;
    }
}
