<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Pagarme\Pagarme\Api\CardsManagementInterface;
use Pagarme\Pagarme\Api\SavedCardRepositoryInterface;
use PagarmeCoreApiLib\APIException;
use Psr\Log\LoggerInterface;

/**
 * Class Remove
 * @package Pagarme\Pagarme\Controller\Cards
 */
class Remove implements HttpGetActionInterface
{
    /** @var SavedCardRepositoryInterface */
    private $savedCardRepository;

    /** @var Session */
    private $_customerSession;

    /** @var Http */
    private $_request;

    /** @var ManagerInterface */
    private $_messageManager;

    /** @var RedirectFactory */
    private $_resultRedirectFactory;

    /** @var CardsManagementInterface */
    private $cardsManagement;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param CardsManagementInterface $cardsManagement
     * @param ManagerInterface $messageManager
     * @param Session $customerSession
     * @param SavedCardRepositoryInterface $savedCardRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param Http $request
     */
    public function __construct(
        LoggerInterface $logger,
        CardsManagementInterface $cardsManagement,
        ManagerInterface $messageManager,
        Session $customerSession,
        SavedCardRepositoryInterface $savedCardRepository,
        RedirectFactory $resultRedirectFactory,
        Http $request
    ) {
        $this->savedCardRepository = $savedCardRepository;
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->cardsManagement = $cardsManagement;
        $this->logger = $logger;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->_resultRedirectFactory->create([ResultFactory::TYPE_REDIRECT]);
        if (!$this->_customerSession->isLoggedIn()) {
            $this->_messageManager->addNoticeMessage(__('You must be logged in.'));
            return $resultRedirect->setPath('customer/account/login');
        }
        if (!$cardId = $this->_request->getParam('card_id')) {
            $this->_messageManager->addErrorMessage(
                __('Unable to find the card.')
            );
            return $resultRedirect->setPath('pagarme/customer/cards');
        }
        try {
            $entity = $this->savedCardRepository->get($cardId);
        } catch (LocalizedException $e) {
            $this->_messageManager->addExceptionMessage(
                $e,
                __('Unable to find the card.')
            );
            return $resultRedirect->setPath('pagarme/customer/cards');
        }
        try {
            $this->cardsManagement->remove($entity);
        } catch (APIException $e) {
            $this->logger->error($e->getMessage());
        }
        try {
            $this->savedCardRepository->delete($entity);
        } catch (LocalizedException $e) {
            $this->_messageManager->addExceptionMessage(
                $e,
                __('Unable to delete the card.')
            );
            return $resultRedirect->setPath('pagarme/customer/cards');
        }
        $this->_messageManager->addSuccessMessage(__('Card removed successfully.'));
        return $resultRedirect->setPath('pagarme/customer/cards');
    }
}
