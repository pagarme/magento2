<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Framework\Controller\ResultInterface;

class Create extends RecipientAction
{
    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {

        $sellers = $this->sellerFactory->create()->getCollection()->load();
        $sellers = $sellers->getItems();
        $this->coreRegistry->register('sellers', serialize($sellers));

        $recipientId = (int)$this->getRequest()->getParam('id');
        if ($recipientId) {
            $this->resourceModelRecipient->load($this->recipient, $recipientId);
            $recipient = $this->recipientService->searchRecipient($this->recipient->getPagarmeId());
            $statusUpdated = false;
            if (empty($this->recipient->getStatus())) {
                $this->recipient->setStatus($recipient->status);
                $this->resourceModelRecipient->save($this->recipient);
                $statusUpdated = true;
            }
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
                    'localId' => $recipientId,
                    'status' => $this->recipient->getStatus(),
                    'statusUpdated' => $statusUpdated
                ])
            );
        }

        $resultPage = $this->resultPageFactory->create();

        $title = $recipientId ? __('Recipient') : __('Create Recipient');

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
