<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\Message\Factory as MagentoMessageFactory;
use Magento\Framework\Module\Manager as ModuleManager;


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
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param MagentoMessageFactory $messageFactory
     * @param ModuleManager $moduleManager
     * @throws Exception
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MagentoMessageFactory $messageFactory,
        ModuleManager $moduleManager
    ) {

        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        $this->moduleManager = $moduleManager;
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
