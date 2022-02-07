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
use Webkul\Marketplace\Model\SellerFactory;

class Create extends Action
{
    protected $resultPageFactory = false;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        SellerFactory $sellerFactory
    ) {
        $this->sellerFactory = $sellerFactory;
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
        $sellers = $this->sellerFactory->create()->getCollection()->load();
        $sellers = $sellers->getItems();

        $this->coreRegistry->register('sellers', serialize($sellers));

        $recipientId = (int)$this->getRequest()->getParam('id');
        if ($recipientId) {

            $recipientService = new RecipientService();
            $recipient = $recipientService->findById($recipientId);
            $recipient = $recipientService->attachBankAccount($recipient);
            $recipient = $recipientService->attachTransferSettings($recipient);

            if (!$recipient || !$recipient->getId()) {
                $this->messageManager->addError(__('Recipient not exist.'));
                $this->_redirect('pagarme_pagarme/recipients/index');
                return;
            }

            $this->coreRegistry->register('recipient_data', json_encode($recipient));
        }
        $resultPage = $this->resultPageFactory->create();

        $title = $recipientId ? __('Edit Recipient') : __('Create Recipient');

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
