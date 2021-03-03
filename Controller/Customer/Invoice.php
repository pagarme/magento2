<?php

namespace Pagarme\Pagarme\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;

class Invoice extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $customerSession,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        $this->coreRegistry->register(
            'code',
            $this->getRequest()->getParam('code')
        );

        $result = $this->pageFactory->create();
        $result->getConfig()->getTitle()->set("Invoices");

        return $result;
    }
}
