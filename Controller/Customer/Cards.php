<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Cards
 * @package Pagarme\Pagarme\Controller\Customer
 */
class Cards implements HttpGetActionInterface
{
    /** @var Session */
    private $_customerSession;

    /** @var RedirectFactory */
    private $_resultRedirectFactory;

    /** @var ManagerInterface */
    private $_messageManager;

    /** @var PageFactory */
    private $_resultPageFactory;

    /** @var RedirectInterface */
    private $_redirect;

    /**
     * @param RedirectInterface $redirect
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     * @param Session $customerSession
     */
    public function __construct(
        RedirectInterface $redirect,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_messageManager = $messageManager;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_redirect = $redirect;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->_resultRedirectFactory->create([ResultFactory::TYPE_REDIRECT]);
        if (!$this->_customerSession->isLoggedIn()) {
            $this->_messageManager->addNoticeMessage(__('You must be logged in.'));
            return $resultRedirect->setPath('customer/account/login');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Cards'));
        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
    }
}
