<?php

namespace Pagarme\Pagarme\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

class Subscription extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var
     */
    protected $context;

    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        $result = $this->pageFactory->create();
        $result->getConfig()->getTitle()->set("Subscription");

        return $result;
    }
}
