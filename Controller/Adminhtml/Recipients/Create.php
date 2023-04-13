<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultInterface;
use Pagarme\Core\Marketplace\Services\RecipientService;
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
        $sellers = $this->sellerFactory->create()->getCollection()->load();
        $sellers = $sellers->getItems();

        $this->coreRegistry->register('sellers', serialize($sellers));

        $recipientId = (int)$this->getRequest()->getParam('id');
        if ($recipientId) {

            $recipientService = new RecipientService();
            $recipient = $recipientService->findById($recipientId);
            $externalId = $recipient->getExternalId();
            $localId = $recipient->getId();
            $recipient = $recipientService->findByPagarmeId($recipient->getPagarmeId());

            if (!$recipient || !$recipient->id) {
                $this->messageManager->addError(__('Recipient not exist.'));
                $this->_redirect('pagarme_pagarme/recipients/index');
                return;
            }

            $this->coreRegistry->register('recipient_data', json_encode(['recipient' => $recipient, 'externalId' => $externalId, 'localId' => $localId]));
        }

        $resultPage = $this->resultPageFactory->create();

        $title = $recipientId ? __('Edit Recipient') : __('Create Recipient');

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
