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
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2SavedCardAdapter;
use Pagarme\Pagarme\Model\CardsRepository;
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

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PageFactory $pageFactory,
        CardsRepository $cardsRepository,
        Session $customerSession,
        Http $request,
        Config $config
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->cardsRepository = $cardsRepository;
        $this->config = $config;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');

            return;
        }

        $idCard = $this->request->getParam('id');
        Magento2CoreSetup::bootstrap();
        $i18n = new LocalizationService();
        $mask = '';

        try {
            $card = $this->cardsRepository->getById($idCard);
            $mask = '****.****.****.' . $card->getLastFourNumbers();

            $matchIds = [];
            preg_match('/mp_core_\d*/', $idCard, $matchIds);
            $deleteService = $this->cardsRepository;
            $deleteMethod = 'deleteById';

            if (isset($matchIds[0])) {
                $mpCardId = filter_var($idCard, FILTER_SANITIZE_NUMBER_INT);
                $savedCardRepository = new SavedCardRepository();
                $savedCard = $savedCardRepository->find($mpCardId);

                $cardAdapter = new Magento2SavedCardAdapter($savedCard);
                $mask = $cardAdapter->getMaskedNumber();

                $deleteService = $this;
                $deleteMethod = 'deleteCoreCard';
            }

            $response =
                $this
                    ->getApi()
                    ->getCustomers()
                    ->deleteCard(
                        $card->getCardId(),
                        $card->getCardToken()
                    );

            $deleteService->$deleteMethod(
                $idCard,
                new NoSuchEntityException(
                    __('Cards with id "%1" does not exist.', $idCard)
                )
            );

            $message = $i18n->getDashboard("The card '%s' was deleted.", $mask);
            $this->messageManager->addSuccess($message);
        } catch (\Exception $e) {
            $logService = new LogService(
                'Card',
                true
            );

            $logService->exception($e);

            $messagesCollection = $this->messageManager->getMessages();
            if (empty($messagesCollection->getItemsByType('success'))) {
                $error = $i18n->getDashboard("The informed card couldn't be deleted.");
                if (!empty($mask)) {
                    $error = $i18n->getDashboard("The card '%s' couldn't be deleted.", $mask);
                }
                $this->messageManager->addError($error);
            }
        }

        $this->_redirect('pagarme/customer/cards');

        return;
    }

    private function deleteCoreCard($coreCardId, $baseException)
    {
        Magento2CoreSetup::bootstrap();

        $savedCardRepository = new SavedCardRepository();

        $matchIds = [];
        preg_match('/mp_core_\d*/', $coreCardId, $matchIds);


        if (!isset($matchIds[0])) {
            throw $baseException;
        }

        $savedCardId = preg_replace('/\D/', '', $matchIds[0]);
        $savedCard = $savedCardRepository->find($savedCardId);
        if ($savedCard === null) {
            throw $baseException;
        }

        $customerId = $this->customerSession->getCustomer()->getId();
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findByCode($customerId);

        if ($customer === null) {
            throw $baseException;
        }

        if (!$customer->getPagarmeId()->equals($savedCard->getOwnerId())) {
            $baseException->setMessage("The logged user doesn't own the informed card.");
            throw $baseException;
        }

        $savedCardRepository->delete($savedCard);
    }

    /**
     * @return \MundiAPILib\MundiAPIClient
     */
    private function getApi()
    {
        return new \MundiAPILib\MundiAPIClient($this->config->getSecretKey(), '');
    }
}
