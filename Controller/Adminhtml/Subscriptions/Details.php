<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Details extends Action
{
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $title = __('Subscription details');

        $subscriptionId = $this->getRequest()->getParam('subscription_id');
        if ($subscriptionId) {
            $title .= ' | ' . $subscriptionId;

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend($title);

            return $resultPage;
        }

        $this->_redirect('pagarme_pagarme/subscriptions/index');
    }
}
