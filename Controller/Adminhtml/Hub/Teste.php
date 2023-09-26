<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Pagarme\Pagarme\Service\AccountService;

class Teste extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;      
    protected $acc;      
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountService $acc
    ) {
        parent::__construct($context);
        $this->acc = $acc;
        $this->resultPageFactory = $resultPageFactory;
    } 
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Demo Menu'));
        var_dump($this->getDetails());
        exit();
        // return $resultPage;
    }

    public function getDetails()
    {
        $acc = $this->acc->getAccount('acc_VdvjEoIXDh94WGgn');
        return $acc;
    }
    protected function _isAllowed()
    {
        return true;
    }
}