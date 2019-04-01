<?php
namespace MundiPagg\MundiPagg\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\CardsRepository;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Helper\Logger;

class Remove extends Action
{
    protected $jsonFactory;

    protected $pageFactory;

    protected $context;

    protected $customerSession;

    protected $request;

    protected $cardsRepository;

    private $config;

    /**
     * @var \MundiPagg\MundiPagg\Helper\Logger
     */
    private $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PageFactory $pageFactory,
        CardsRepository $cardsRepository,
        Session $customerSession,
        Http $request,
        Config $config,
        Logger $logger
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->cardsRepository = $cardsRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login'); 

            return;
        }

        $idCard = $this->request->getParam('id');

        try {
            $card = $this->cardsRepository->getById($idCard);

            $matchIds = [];
            preg_match('/mp_core_\d*/', $idCard, $matchIds);

            if (!isset($matchIds[0])) {
                $result = $this->cardsRepository->deleteById($idCard);
            }
            else {
                $this->deleteCoreCard(
                    $idCard,
                    new NoSuchEntityException(
                        __('Cards with id "%1" does not exist.', $idCard)
                    )
                );
            }

            $response =
            $this
                ->getApi()
                ->getCustomers()
                ->deleteCard(
                    $card->getCardId(),
                    $card->getCardToken()
                );
            $this->logger->logger($response);

            $this->messageManager->addSuccess(__('You deleted card id: %1', $idCard));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('mundipagg/customer/cards'); 

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

        if (!$customer->getMundipaggId()->equals($savedCard->getOwnerId())) {
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