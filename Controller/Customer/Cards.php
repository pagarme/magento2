<?php
namespace Pagarme\Pagarme\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

class Cards extends Action
{
    protected $jsonFactory;

    protected $pageFactory;

    protected $context;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        JsonFactory $jsonFactory,
        PageFactory $pageFactory,
        Session $customerSession
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');

            return; # code...
        }
        $result = $this->pageFactory->create();
        $result->getConfig()->getTitle()->set("My Cards");

        return $result;
    }

}
