<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Recipients;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Pagarme\Pagarme\Model\Api\Recipient;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Pagarme_Pagarme::recipients_save';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Recipient
     */
    private $recipient;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Recipient $recipient
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->recipient = $recipient;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $form = $this->getRequest()->getPostValue('form') ?: [];

        return $this->resultJsonFactory->create()
            ->setData($this->recipient->registerRecipient($form));
    }
}
