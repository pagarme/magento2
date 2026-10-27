<?php
namespace Pagarme\Pagarme\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2SavedCardAdapter;
use Pagarme\Pagarme\Model\CardsRepository;
use Pagarme\Pagarme\Model\Customer\CardOwnership;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;

class Remove extends Action
{
    protected $jsonFactory;

    protected $pageFactory;

    protected $context;

    protected $customerSession;

    protected $request;

    protected $cardsRepository;

    private $config;

    /** @var CardOwnership */
    private $cardOwnership;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PageFactory $pageFactory,
        CardsRepository $cardsRepository,
        Session $customerSession,
        Http $request,
        Config $config,
        CardOwnership $cardOwnership
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->cardsRepository = $cardsRepository;
        $this->config = $config;
        $this->cardOwnership = $cardOwnership;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');

            return;
        }

        $cardId = $this->request->getParam('id');
        Magento2CoreSetup::bootstrap();
        $i18n = new LocalizationService();
        $mask = '';

        try {
            $card = $this->cardsRepository->getById($cardId);
            // Ownership is checked before any gateway call.
            $mask = $this->isCoreCardId($cardId)
                ? $this->authorizeCoreCard($cardId)
                : $this->authorizeLegacyCard($card);

            $this->deleteFromGateway($card);
            $this->deleteLocally($cardId);

            $message = $i18n->getDashboard("The card '%s' was deleted.", $mask);
            $this->messageManager->addSuccess($message);
        } catch (\Exception $e) {
            $this->reportFailure($e, $i18n, $mask);
        }

        $this->_redirect('pagarme/customer/cards');
    }

    private function isCoreCardId(?string $cardId): bool
    {
        return (bool) preg_match('/mp_core_\d*/', $cardId ?? '');
    }

    /**
     * @return string masked number of the card
     * @throws NoSuchEntityException when the card is not owned by the logged-in customer
     */
    private function authorizeCoreCard(string $cardId): string
    {
        $savedCard = $this->findCoreCard($cardId);
        if ($savedCard === null
            || !$this->cardOwnership->ownsPagarmeId($savedCard->getOwnerId()->getValue())
        ) {
            throw $this->cardNotFound();
        }

        return (new Magento2SavedCardAdapter($savedCard))->getMaskedNumber();
    }

    /**
     * @return string masked number of the card
     * @throws NoSuchEntityException when the card is not owned by the logged-in customer
     */
    private function authorizeLegacyCard($card): string
    {
        if (!$this->cardOwnership->ownsCustomerId($card->getCustomerId())) {
            throw $this->cardNotFound();
        }

        return '****.****.****.' . $card->getLastFourNumbers();
    }

    private function deleteFromGateway($card): void
    {
        $this->getApi()
            ->getCustomers()
            ->deleteCard($card->getCardId(), $card->getCardToken());
    }

    private function deleteLocally(string $cardId): void
    {
        if ($this->isCoreCardId($cardId)) {
            $this->deleteCoreCard($cardId);
            return;
        }

        $this->cardsRepository->deleteById($cardId);
    }

    private function deleteCoreCard(string $cardId): void
    {
        $savedCard = $this->findCoreCard($cardId);
        if ($savedCard === null) {
            throw $this->cardNotFound();
        }

        (new SavedCardRepository())->delete($savedCard);
    }

    /**
     * Core cards are stored as "mp_core_<id>"; only the numeric part is the primary key.
     */
    private function findCoreCard(string $cardId)
    {
        preg_match('/mp_core_\d*/', $cardId, $matchIds);
        $savedCardId = preg_replace('/\D/', '', $matchIds[0] ?? '');

        return (new SavedCardRepository())->find($savedCardId);
    }

    private function reportFailure(\Exception $e, LocalizationService $i18n, string $mask): void
    {
        (new LogService('Card', true))->exception($e);

        $messages = $this->messageManager->getMessages();
        if (!empty($messages->getItemsByType('success'))) {
            return;
        }

        $this->messageManager->addError($this->failureMessage($e, $i18n, $mask));
    }

    private function failureMessage(\Exception $e, LocalizationService $i18n, string $mask)
    {
        if ($e instanceof NoSuchEntityException) {
            return __('The card does not exist.');
        }

        if ($mask !== '') {
            return $i18n->getDashboard("The card '%s' couldn't be deleted.", $mask);
        }

        return $i18n->getDashboard("The informed card couldn't be deleted.");
    }

    private function cardNotFound(): NoSuchEntityException
    {
        return new NoSuchEntityException(__('The card does not exist.'));
    }

    /**
     * @return \PagarmeCoreApiLib\PagarmeCoreApiClient
     */
    private function getApi()
    {
        return new \PagarmeCoreApiLib\PagarmeCoreApiClient($this->config->getSecretKey(), '');
    }
}
