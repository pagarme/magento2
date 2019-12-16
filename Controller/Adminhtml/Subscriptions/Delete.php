<?php

namespace MundiPagg\MundiPagg\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\ProductsSubscriptionFactory;
use Magento\Framework\HTTP\ZendClientFactory;

class Delete extends Action
{
    const URL = '/rest/default/V1/mundipagg/subscription/cancel';

    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    protected $messageFactory;

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
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        Magento2CoreSetup::bootstrap();

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

        $httpClient = new ZendClientFactory();
        $apiCaller = $httpClient->create();
        $apiCaller->setUri(self::URL . '/' . $id);
        $apiCaller->setMethod(\Zend_Http_Client::GET);
        $apiCaller->setHeaders([
            'Content-Type: application/json'
        ]);

        $message = $this->messageFactory->create(
            MessageInterface::TYPE_ERROR,
            _("Subscription ERROR.")
        );

        if ($apiCaller->request()->getStatus() == 200) {
            $message = $this->messageFactory->create(
                MessageInterface::TYPE_SUCCESS,
                _("Subscription deleted.")
            );
        }

        $this->messageManager->addMessage($message);
        $this->_redirect('mundipagg_mundipagg/subscriptions/index');
        return;
    }
}
