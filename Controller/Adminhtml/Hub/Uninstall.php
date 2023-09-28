<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Pagarme\Pagarme\Model\Api\HubCommand;

class Uninstall extends \Magento\Backend\App\Action
{


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HubCommand $hubCommand
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->hubCommand = $hubCommand;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->hubCommand->uninstallCommand();
        $url = $this->getUrl('adminhtml/system_config/edit/section/payment');
        header('Location: ' . explode('?', $url ?? '')[0] . 'website/1');
        exit;
    }

    protected function _isAllowed()
    {
        return true;
    }

}
