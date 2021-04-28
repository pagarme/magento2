<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\ProductsSubscriptionFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use Pagarme\Core\Recurrence\Services\SubscriptionService;

class Delete extends Action
{
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Factory
     */
    protected $messageFactory;

    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @throws \Exception
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        Factory $messageFactory
    ) {
        Magento2CoreSetup::bootstrap();
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        $this->subscriptionService = new SubscriptionService();

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $message = $this->messageFactory->create(
            MessageInterface::TYPE_ERROR,
            _("Unable to cancel subscription")
        );

        $subscription = $this->subscriptionService->cancel($id);

        if ($subscription['code'] == 200) {
            $message = $this->messageFactory->create(
                MessageInterface::TYPE_SUCCESS,
                _("Subscription deleted.")
            );
        }

        $this->messageManager->addMessage($message);
        $this->_redirect('pagarme_pagarme/subscriptions/index');
        return;
    }
}
