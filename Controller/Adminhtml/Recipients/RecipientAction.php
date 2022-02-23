<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\Message\Factory as MagentoMessageFactory;

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
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MagentoMessageFactory $messageFactory,
        SellerFactory $sellerFactory
    ) {

        parent::__construct($context);
        $this->sellerFactory = $sellerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        Magento2CoreSetup::bootstrap();
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    }
}
