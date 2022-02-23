<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;


use Pagarme\Pagarme\Controller\Adminhtml\Recipients\RecipientAction;
use Pagarme\Core\Marketplace\Services\RecipientService;

class Delete extends RecipientAction
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $recipientId = (int)$this->getRequest()->getParam('id');
        if ($recipientId) {

            $recipientService = new RecipientService();
            $recipientData = $recipientService->findById($recipientId);

            if (!$recipientData || !$recipientData->getId()) {
                $message = $this->messageFactory->create('error', __('Recipient not exist.'));
                $this->messageManager->addErrorMessage($message);
                $this->_redirect('pagarme_pagarme/recipients/index');
                return;
            }
        }

        $recipientService->delete($recipientId);

        $message = $this->messageFactory->create('success', _("Recipient deleted."));
        $this->messageManager->addMessage($message);

        $this->_redirect('pagarme_pagarme/recipients/index');
        return;
    }
}
