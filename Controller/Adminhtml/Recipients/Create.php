<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultInterface;
use Webkul\Marketplace\Model\SellerFactory;
use Pagarme\Pagarme\Controller\Adminhtml\Recipients\RecipientAction;

class Create extends RecipientAction
{
    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $recipientId = (int)$this->getRequest()->getParam('id');
        if ($recipientId) {
            $this->resourceModelRecipient->load($this->recipient, $recipientId);
            $recipient = $this->recipientService->searchRecipient($this->recipient->getPagarmeId());
            if (!$recipient || !$recipient->id) {
                $this->messageManager->addError(__('Recipient not exist.'));
                $this->_redirect('pagarme_pagarme/recipients/index');
                return;
            }

            $this->coreRegistry->register(
                'recipient_data',
                json_encode([
                    'recipient' => $recipient,
                    'externalId' => $recipient->code, 
                    'localId' => $recipientId
                ])
            );
        }

        $resultPage = $this->resultPageFactory->create();

        $title = $recipientId ? __('Edit Recipient') : __('Create Recipient');

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
