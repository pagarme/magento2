<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Create extends Action
{
    protected $resultPageFactory = false;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        Magento2CoreSetup::bootstrap();

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
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
