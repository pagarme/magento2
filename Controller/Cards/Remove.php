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
use Magento\Framework\Phrase;
use Pagarme\Pagarme\Api\CardsManagementInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use Pagarme\Pagarme\Api\SavedCardRepositoryInterface;
use Pagarme\Pagarme\Model\Customer\CardOwnership;
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

    /** @var CardOwnership */
    private $cardOwnership;

    /**
     * @param LoggerInterface $logger
     * @param CardsManagementInterface $cardsManagement
     * @param ManagerInterface $messageManager
     * @param Session $customerSession
     * @param SavedCardRepositoryInterface $savedCardRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param Http $request
     * @param CardOwnership $cardOwnership
     */
    public function __construct(
        LoggerInterface $logger,
        CardsManagementInterface $cardsManagement,
        ManagerInterface $messageManager,
        Session $customerSession,
        SavedCardRepositoryInterface $savedCardRepository,
        RedirectFactory $resultRedirectFactory,
        Http $request,
        CardOwnership $cardOwnership
    ) {
        $this->savedCardRepository = $savedCardRepository;
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->cardsManagement = $cardsManagement;
        $this->logger = $logger;
        $this->cardOwnership = $cardOwnership;
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->_messageManager->addNoticeMessage(__('You must be logged in.'));
            return $this->redirectTo('customer/account/login');
        }

        $cardId = $this->_request->getParam('card_id');
        if (!$cardId) {
            return $this->redirectWithError(__('Unable to find the card.'));
        }

        $entity = $this->findOwnedCard($cardId);
        if ($entity === null) {
            return $this->redirectWithError(__('The card does not exist.'));
        }

        $this->removeFromGateway($entity);

        try {
            $this->savedCardRepository->delete($entity);
        } catch (LocalizedException $e) {
            $this->_messageManager->addExceptionMessage($e, __('Unable to delete the card.'));
            return $this->redirectToCards();
        }

        $this->_messageManager->addSuccessMessage(__('Card removed successfully.'));
        return $this->redirectToCards();
    }

    /**
     * Missing and foreign cards both return null, so the response does not reveal which ids exist.
     */
    private function findOwnedCard(string $cardId): ?SavedCardInterface
    {
        try {
            $card = $this->savedCardRepository->get($cardId);
        } catch (LocalizedException $e) {
            return null;
        }

        return $this->cardOwnership->ownsPagarmeId($card->getOwnerId()) ? $card : null;
    }

    private function removeFromGateway(SavedCardInterface $entity): void
    {
        try {
            $this->cardsManagement->remove($entity);
        } catch (APIException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function redirectWithError(Phrase $message): Redirect
    {
        $this->_messageManager->addErrorMessage($message);

        return $this->redirectToCards();
    }

    private function redirectToCards(): Redirect
    {
        return $this->redirectTo('pagarme/customer/cards');
    }

    private function redirectTo(string $path): Redirect
    {
        return $this->_resultRedirectFactory
            ->create([ResultFactory::TYPE_REDIRECT])
            ->setPath($path);
    }
}
