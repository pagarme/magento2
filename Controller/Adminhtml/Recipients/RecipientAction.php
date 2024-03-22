<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Pagarme\Pagarme\Model\Recipient;
use Magento\Backend\App\Action\Context;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Framework\Module\Manager as ModuleManager;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use Magento\Framework\Message\Factory as MagentoMessageFactory;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;

class RecipientAction extends Action
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
     * @var MagentoMessageFactory
     */
    protected $messageFactory;

    /**
     * @var ModuleManager
     */
    private $moduleManager;
    /**
     *
     * @var \Pagarme\Pagarme\Model\ResourceModel\Recipients
     */
    protected $resourceModelRecipient;
    /**
     *
     * @var \Pagarme\Pagarme\Model\Recipient
     */
    protected $recipient;
    /**
     *
     * @var \Pagarme\Pagarme\Service\Marketplace\RecipientService
     */
    protected $recipientService;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param MagentoMessageFactory $messageFactory
     * @param ModuleManager $moduleManager
     * @param ResourceModelRecipient $resourceModelRecipient
     * @param Recipient $recipient
     * @param RecipientService $recipientService
     * @throws Exception
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MagentoMessageFactory $messageFactory,
        ModuleManager $moduleManager,
        ResourceModelRecipient $resourceModelRecipient,
        Recipient $recipient,
        RecipientService $recipientService
    ) {

        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        $this->moduleManager = $moduleManager;
        $this->resourceModelRecipient = $resourceModelRecipient;
        $this->recipient = $recipient;
        $this->recipientService = $recipientService;
        $this->__init();
        
        Magento2CoreSetup::bootstrap();
    }

    private function __init()
    {
        if($this->moduleManager->isEnabled("Webkul_Marketplace")){
            $this->sellerFactory = $this->_objectManager->create(SellerFactory::class);
        }
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    }
}
