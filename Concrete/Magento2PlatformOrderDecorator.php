<?php

namespace Pagarme\Pagarme\Concrete;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Payment\Aggregates\Address;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Aggregates\Item;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractPayment;
use Pagarme\Core\Payment\Aggregates\Payments\BoletoPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewDebitCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewVoucherPayment;
use Pagarme\Core\Payment\Aggregates\Payments\PixPayment;
use Pagarme\Core\Payment\Aggregates\Shipping;
use Pagarme\Core\Payment\Factories\PaymentFactory;
use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Pagarme\Core\Payment\ValueObjects\Phone;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Helper\BuildChargeAddtionalInformationHelper;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Pagarme\Pagarme\Model\CardsRepository;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Quote\Model\Quote;
use Pagarme\Pagarme\Helper\Marketplace\WebkulHelper;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Pagarme\Pagarme\Model\Source\Bank;
use stdClass;

class Magento2PlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var Order */
    protected $platformOrder;

    /**
     * @var Order
     */
    private $orderFactory;
    private $quote;
    private $i18n;

    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var PagarmeConfigProvider
     */
    private $config;
    /**
     * @var MoneyService
     */
    private $moneyService;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
        $objectManager = ObjectManager::getInstance();
        $this->moneyService = new MoneyService();
        $this->config = $objectManager->get('Pagarme\Pagarme\Model\PagarmeConfigProvider');
        $this->orderFactory = $objectManager->get('Magento\Sales\Model\Order');
        $this->orderService = new OrderService();
        parent::__construct();
    }

    public function save()
    {
        /*
         * @fixme Saving order this way in magento2 is deprecated.
         *        Find out how to fix this.
         */
        $this->platformOrder->save();
    }

    public function setStateAfterLog(OrderState $state)
    {
        $stringState = $state->getState();
        $this->platformOrder->setState($stringState);
    }


    /**
     * @return OrderState;
     */
    public function getState()
    {
        $baseState = explode('_', $this->getPlatformOrder()->getState() ?? '');
        $state = '';
        foreach ($baseState as $st) {
            $state .= ucfirst($st);
        }
        $state = lcfirst($state);

        if ($state === Order::STATE_NEW) {
            $state = 'stateNew';
        }

        return OrderState::$state();
    }

    public function setStatusAfterLog(OrderStatus $status)
    {
        $stringStatus = $status->getStatus();
        $this->platformOrder->setStatus($stringStatus);
    }

    public function getStatus()
    {
        return $this->getPlatformOrder()->getStatus();
    }

    public function loadByIncrementId($incrementId)
    {
        $this->platformOrder =
            $this->orderFactory->loadByIncrementId($incrementId);
    }

    /**
     * @param string $message
     * @return bool
     */
    public function sendEmail($message)
    {
        $log = new LogService('Order', true);
        $log->info("Try send e-mail: {$message}");

        try {
            $objectManager = ObjectManager::getInstance();

            $sendConfigGlobalEmail = MPSetup::getModuleConfiguration()->isSendMailEnabled();

            if (!$sendConfigGlobalEmail) {
                $log->info("The e-mail sending configuration is disabled. E-mail not sent");
                return false;
            }

            /* @var OrderCommentSender $orderCommentSender */
            $orderCommentSender = $objectManager->create(OrderCommentSender::class);

            return $orderCommentSender->send(
                $this->platformOrder,
                true,
                $message
            );
        } catch (\Exception $e) {
            $log->info("Unable to send e-mail");
            $log->exception($e);
        }
    }

    /**
     * @param OrderStatus $orderStatus
     * @return string
     */
    public function getStatusLabel(OrderStatus $orderStatus)
    {
        $objectManager = ObjectManager::getInstance();

        /* @var Collection $statusCollection */
        $statusCollection = $objectManager->create(Collection::class);

        $optionsStatusArray = $statusCollection->toOptionArray();

        foreach ($optionsStatusArray as $optionStatus) {
            if ($optionStatus['value'] == $orderStatus->getStatus()) {
                return $optionStatus['label'];
            }
        }

        return $orderStatus->getStatus();
    }

    /**
     * @param $message
     * @param bool $notifyCustomer
     */
    protected function addMPHistoryComment($message, $notifyCustomer = false)
    {
        $historyMethod = 'addCommentToStatusHistory';
        if (!method_exists($this->platformOrder, $historyMethod)) {
            $historyMethod = 'addStatusHistoryComment';
        }

        $this->platformOrder->$historyMethod($message)
            ->setIsCustomerNotified($notifyCustomer)
            ->save();
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setAdditionalInformation($name, $value)
    {
        $this->platformOrder
            ->getPayment()
            ->setAdditionalInformation($name, $value)
            ->save();
    }

    /**
     * @param Charge[] $charges
     * @return array[['key' => value]]
     */
    public function extractAdditionalChargeInformation(array $charges)
    {
        return BuildChargeAddtionalInformationHelper::build(
            $this->getPaymentMethodPlatform(),
            $charges
        );
    }

    /**
     * @param Charge[] $charges
     */
    public function addAdditionalInformation(array $charges)
    {
        $chargesAddtionalInformation = $this->extractAdditionalChargeInformation(
            $charges
        );

        foreach ($chargesAddtionalInformation as $chargesInformation) {
            foreach ($chargesInformation as $propertyName => $value) {
                $this->setAdditionalInformation(
                    $propertyName,
                    $value
                );
            }
        }
    }

    public function setIsCustomerNotified()
    {
        // TODO: Implement setIsCustomerNotified() method.
    }

    public function canInvoice()
    {
        return $this->platformOrder->canInvoice();
    }

    public function getIncrementId()
    {
        return $this->getPlatformOrder()->getIncrementId();
    }

    public function getGrandTotal()
    {
        return $this->getPlatformOrder()->getGrandTotal();
    }


    public function getBaseTaxAmount()
    {
        return $this->getPlatformOrder()->getBaseTaxAmount();
    }

    public function getTotalPaid()
    {
        return $this->getPlatformOrder()->getTotalPaid();
    }

    public function getTotalDue()
    {
        return $this->getPlatformOrder()->getTotalDue();
    }

    public function setTotalPaid($amount)
    {
        $this->getPlatformOrder()->setTotalPaid($amount);
    }

    public function setBaseTotalPaid($amount)
    {
        $this->getPlatformOrder()->setBaseTotalPaid($amount);
    }

    public function setTotalDue($amount)
    {
        $this->getPlatformOrder()->setTotalDue($amount);
    }

    public function setBaseTotalDue($amount)
    {
        $this->getPlatformOrder()->setBaseTotalDue($amount);
    }

    public function setTotalCanceled($amount)
    {
        $this->getPlatformOrder()->setTotalCanceled($amount);
    }

    public function setBaseTotalCanceled($amount)
    {
        $this->getPlatformOrder()->setBaseTotalCanceled($amount);
    }

    public function getTotalRefunded()
    {
        return $this->getPlatformOrder()->getTotalRefunded();
    }

    public function setTotalRefunded($amount)
    {
        $this->getPlatformOrder()->setTotalRefunded($amount);
    }

    public function setBaseTotalRefunded($amount)
    {
        $this->getPlatformOrder()->setBaseTotalRefunded($amount);
    }

    public function getCode()
    {
        return $this->getPlatformOrder()->getIncrementId();
    }

    public function canUnhold()
    {
        return $this->getPlatformOrder()->canUnhold();
    }

    public function isPaymentReview()
    {
        return $this->getPlatformOrder()->isPaymentReview();
    }

    public function isCanceled()
    {
        return $this->getPlatformOrder()->isCanceled();
    }

    /**
     * @return string
     */
    public function getPaymentMethodPlatform()
    {
        return $this->getPlatformOrder()->getPayment()->getMethod();
    }

    /**
     * @return PlatformInvoiceInterface[]
     */
    public function getInvoiceCollection()
    {
        $baseInvoiceCollection = $this->platformOrder->getInvoiceCollection();

        $invoiceCollection = [];
        foreach ($baseInvoiceCollection as $invoice) {
            $invoiceCollection[] = new Magento2PlatformInvoiceDecorator($invoice);
        }

        return $invoiceCollection;
    }

    /**
     * @return OrderId|null
     */
    public function getPagarmeId()
    {
        $orderId = null;

        if ($this->platformOrder->getPayment() != null) {
            $orderId = $this->platformOrder->getPayment()->getLastTransId();
        }

        if (!empty($orderId)) {
            $orderId = substr($orderId, 0, 19);
            return new OrderId($orderId);
        }

        $orderCore = $this->orderService->getOrderByPlatformId(
            $this->platformOrder->getIncrementId()
        );

        if ($orderCore == null) {
            return $orderId;
        }

        return $orderCore->getPagarmeId();
    }

    public function getHistoryCommentCollection()
    {
        $baseHistoryCollection = $this->platformOrder->getStatusHistoryCollection();

        $historyCollection = [];
        foreach ($baseHistoryCollection as $history) {
            $historyCollection[] = $history->getData();
        }

        return $historyCollection;
    }

    public function getData()
    {
        return $this->platformOrder->getData();
    }

    public function getTransactionCollection()
    {
        $objectManager = ObjectManager::getInstance();
        $transactionRepository = $objectManager->get(TransactionRepository::class);
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);;
        $filterBuilder = $objectManager->get(FilterBuilder::class);

        $filters[] = $filterBuilder->setField('payment_id')
            ->setValue($this->platformOrder->getPayment()->getId())
            ->create();

        $filters[] = $filterBuilder->setField('order_id')
            ->setValue($this->platformOrder->getId())
            ->create();

        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $baseTransactionCollection = $transactionRepository->getList($searchCriteria);

        $transactionCollection = [];
        foreach ($baseTransactionCollection as $transaction) {
            $transactionCollection[] = $transaction->getData();
        }

        return $transactionCollection;
    }

    public function getPaymentCollection()
    {
        $objectManager = ObjectManager::getInstance();
        $paymentRepository = $objectManager->get(PaymentRepository::class);
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);;
        $filterBuilder = $objectManager->get(FilterBuilder::class);

        $filters[] = $filterBuilder->setField('parent_id')
            ->setValue($this->platformOrder->getId())
            ->create();

        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $basePaymentCollection = $paymentRepository->getList($searchCriteria);

        $paymentCollection = [];
        foreach ($basePaymentCollection as $payment) {
            $paymentCollection[] = $payment->getData();
        }

        return $paymentCollection;
    }

    /** @return Customer */
    public function getCustomer()
    {
        $quote = $this->getQuote();

        $quoteCustomer = $quote->getCustomer();

        $method = 'getRegisteredCustomer';
        if ($quoteCustomer->getId() === null) {
            $method = 'getGuestCustomer';
        }

        return $this->$method($quote);
    }

    /**
     * @param Quote $quote
     * @return Customer
     * @throws \Exception
     */
    private function getRegisteredCustomer($quote)
    {
        $quoteCustomer = $quote->getCustomer();

        $addresses = $quoteCustomer->getAddresses();
        $address = end($addresses);

        if (!$address) {
            $address = $quote->getBillingAddress();
        }

        $customerRepository =
            ObjectManager::getInstance()->get(CustomerRepository::class);
        $savedCustomer = $customerRepository->getById($quoteCustomer->getId());

        $customer = new Customer;
        $customer->setCode($savedCustomer->getId());

        $mpId = null;
        try {
            $mpId = $savedCustomer->getCustomAttribute('customer_id_pagarme')
                ->getValue();
            $customerId = new CustomerId($mpId);
            $customer->setPagarmeId($customerId);
        } catch (\Throwable $e) {
        }

        if (empty($mpId)) {
            $coreCustomerRespository = new CoreCustomerRepository();
            $coreCustomer = $coreCustomerRespository->findByCode(
                $savedCustomer->getId()
            );
            if ($coreCustomer !== null) {
                $customer->setPagarmeId($coreCustomer->getPagarmeId());
            }
        }

        $fullName = implode(' ', [
            $quote->getCustomerFirstname(),
            $quote->getCustomerMiddlename(),
            $quote->getCustomerLastname(),
        ]);

        if ($fullName && !is_null($fullName)) {
            $fullName = preg_replace("/  /", " ", $fullName);
        }

        $customer->setName($fullName);
        $customer->setEmail($quote->getCustomerEmail());
        $customerDocument = $this->cleanCustomerDocument(
            $address->getVatId() ?? ""
        );

        if (!$customerDocument) {
            $customerDocument = $this->cleanCustomerDocument(
                $quote->getCustomer()->getTaxVat() ?? ""
            );
        }
        $customer->setDocument($customerDocument);
        $customer->setType(CustomerType::individual());

        $telephone = $address->getTelephone();
        $phone = new Phone($telephone);

        $customer->setPhones(
            CustomerPhones::create([$phone, $phone])
        );

        $address = $this->getAddress($address);

        $customer->setAddress($address);

        return $customer;
    }

    /**
     * @param string $document
     * @return array|string|string[]|null
     */
    public function cleanCustomerDocument(string $document)
    {
        if(is_null($document)) {
            return '';
        }
        return preg_replace(
            '/\D/',
            '',
            $document
        );
    }

    /**
     * @param Quote $quote
     * @return Customer
     * @throws \Exception
     */
    private function getGuestCustomer($quote)
    {
        $guestAddress = $quote->getBillingAddress();

        $customer = new Customer();

        $customer->setName($guestAddress->getName());
        $customer->setEmail($guestAddress->getEmail());

        $customerDocument = $guestAddress->getVatId();

        if (!$customerDocument) {
            $customerDocument = $quote->getCustomerTaxvat();
        }

        if (!is_null($customerDocument)) {
            $customerDocument = $this->cleanCustomerDocument($customerDocument);
        }

        $customer->setDocument($customerDocument);
        $customer->setType(CustomerType::individual());

        $telephone = $guestAddress->getTelephone();
        $phone = new Phone($telephone);

        $customer->setPhones(
            CustomerPhones::create([$phone, $phone])
        );

        $address = $this->getAddress($guestAddress);
        $customer->setAddress($address);

        return $customer;
    }

    /** @return Item[] */
    public function getItemCollection()
    {
        $quote = $this->getQuote();
        $itemCollection = $quote->getItemsCollection();
        $hasSubscriptionItem = false;
        $items = [];
        $selectedRepetition = null;
        foreach ($itemCollection as $quoteItem) {
            //adjusting price.
            $price = $quoteItem->getPrice();
            $price = $price > 0 ? $price : "0.01";

            if ($price === null) {
                continue;
            }

            /**
             * Bundle product
             */
            if (
                !empty($quoteItem->getParentItemId()) &&
                $quoteItem->getProductType() === 'simple'
            ) {
                continue;
            }

            $item = new Item;
            $item->setAmount(
                $this->moneyService->floatToCents($price)
            );

            if ($quoteItem->getProductId()) {
                $item->setCode($quoteItem->getProductId());
            }

            $item->setQuantity(intval($quoteItem->getQty()));
            $item->setDescription(
                $quoteItem->getName() . ' : ' .
                    $quoteItem->getDescription()
            );

            $item->setName($quoteItem->getName());
            $recurrenceItem = $this->getRecurrenceService()
                ->getRecurrenceProductByProductId($quoteItem->getProductId());
            if ($recurrenceItem) {
                $helper = new RecurrenceProductHelper();
                $selectedRepetition = $helper->getSelectedRepetition($quoteItem);
                $item->setSelectedOption($selectedRepetition);
                $this->setRecurrenceInfo($item, $quoteItem);

                $hasSubscriptionItem = $hasSubscriptionItem || !empty($item->getType());

                if ($item->getType() === Plan::RECURRENCE_TYPE) {
                    $planItems = $recurrenceItem->getItems();
                    $cycles = $this->getRecurrenceService()
                        ->getGreatestCyclesFromItems($planItems);
                    $selectedRepetition = new Repetition();
                    $selectedRepetition->setCycles($cycles);
                }
            }
            $items[] = $item;
        }

        return $this->addShippingAndTaxToSubscription($hasSubscriptionItem, $selectedRepetition, $items);
    }

    private function addShippingAndTaxToSubscription($hasSubscriptionItem, $selectedRepetition, $items)
    {
        if (!$hasSubscriptionItem) {
            return $items;
        }

        if ($this->getPlatformOrder()->getShippingAmount() && $this->config->canAddShippingInItemsOnRecurrence()) {
            $items[] = $this->addCustomItem(
                $this->getPlatformOrder()->getShippingAmount(),
                $this->getPlatformOrder()->getShippingDescription(),
                $selectedRepetition
            );
        }
        if ($this->getPlatformOrder()->getBaseTaxAmount() && $this->config->canAddTaxInItemsOnRecurrence()) {
            $items[] = $this->addCustomItem(
                $this->getPlatformOrder()->getBaseTaxAmount(),
                __("Taxs"),
                $selectedRepetition
            );
        }

        return $items;
    }

    private function addCustomItem($value, $name, $selectedRepetition)
    {
        $product = new Item();
        $product->setName($name);
        $product->setDescription($name);
        $product->setQuantity(1);
        $product->setCode(0);
        $product->setSelectedOption($this->mountRepetition($value, $selectedRepetition));
        $product->setType("subscription");
        $product->setAmount($this->moneyService->floatToCents($value));
        return $product;
    }
    private function mountRepetition($value, $selectedRepetition)
    {
        if (empty($selectedRepetition)) {
            return $selectedRepetition;
        }
        $selectedRepetition->setRecurrencePrice($this->moneyService->floatToCents($value));
        return $selectedRepetition;
    }

    public function setRecurrenceInfo($item, $quoteItem)
    {
        $recurrenceService = $this->getRecurrenceService();
        $productId = $quoteItem->getProduct()->getId();

        $coreProduct =
            $recurrenceService->getRecurrenceProductByProductId(
                $productId
            );

        if (!$coreProduct) {
            return null;
        }

        $type = $coreProduct->getRecurrenceType();

        if ($type == Plan::RECURRENCE_TYPE) {
            $item->setPagarmeId($coreProduct->getPagarmeId());
            $item->setType($type);
            return $item;
        }

        if (!empty($item->getSelectedOption())) {
            $item->setType($type);
        }

        return $item;
    }

    public function getRecurrenceService()
    {
        return new RecurrenceService();
    }

    public function getQuote()
    {
        if ($this->quote === null) {
            $quoteId = $this->platformOrder->getQuoteId();

            $objectManager = ObjectManager::getInstance();
            $quoteFactory = $objectManager->get(QuoteFactory::class);
            $this->quote = $quoteFactory->create()->load($quoteId);
        }

        return $this->quote;
    }

    /** @return AbstractPayment[] */
    public function getPaymentMethodCollection()
    {
        $payments = $this->getPaymentCollection();

        if (empty($payments)) {
            $baseNewPayment = $this->platformOrder->getPayment();

            $newPayment = [];
            $newPayment['method'] = $baseNewPayment->getMethod();
            $newPayment['additional_information'] =
                $baseNewPayment->getAdditionalInformation();
            $payments = [$newPayment];
        }

        $paymentData = [];

        foreach ($payments as $payment) {
            $handler = explode('_', $payment['method'] ?? '');
            array_walk($handler, function (&$part) {
                $part = ucfirst($part);
            });
            $handler = 'extractPaymentDataFrom' . implode('', $handler);
            $this->$handler(
                $payment['additional_information'],
                $paymentData,
                $payment
            );
        }

        $paymentFactory = new PaymentFactory();
        $paymentMethods = $paymentFactory->createFromJson(
            json_encode($paymentData)
        );
        return $paymentMethods;
    }

    private function extractPaymentDataFromPagarmeCreditCard(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeVoucher(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

        $creditCardDataIndex = NewVoucherPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeDebit(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

        $creditCardDataIndex = NewDebitCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractBasePaymentData($additionalInformation)
    {
        $identifier = null;
        $customerId = null;
        $brand = null;

        try {
            $brand = strtolower($additionalInformation['cc_type']);
        } catch (\Exception $e) {
            // do nothing
        } catch (\Throwable $e) {
            // do nothing
        }

        if (isset($additionalInformation['cc_token_credit_card'])) {
            $identifier = $additionalInformation['cc_token_credit_card'];
        }
        if (
            !empty($additionalInformation['cc_saved_card']) &&
            $additionalInformation['cc_saved_card'] !== null
        ) {
            $identifier = null;
        }

        if ($identifier === null) {
            $objectManager = ObjectManager::getInstance();
            $cardRepo = $objectManager->get(CardsRepository::class);
            $cardId = $additionalInformation['cc_saved_card'];
            $card = $cardRepo->getById($cardId);

            $identifier = $card->getCardToken();
            $customerId = $card->getCardId();
        }

        $newPaymentData = new stdClass();
        $newPaymentData->customerId = $customerId;
        $newPaymentData->brand = $brand;
        $newPaymentData->identifier = $identifier;
        $newPaymentData->installments = $additionalInformation['cc_installments'];
        $newPaymentData->saveOnSuccess =
            isset($additionalInformation['cc_savecard']) &&
            $additionalInformation['cc_savecard'] === '1';

        if (isset($additionalInformation['cc_cvv_card']) && !empty($additionalInformation['cc_cvv_card'])) {
            $newPaymentData->cvvCard = $additionalInformation['cc_cvv_card'];
        }

        if (!empty($additionalInformation['authentication'])) {
            $additionalInformation['authentication'] = json_decode($additionalInformation['authentication'], true);
            $authentication = new stdClass();
            $authentication->type = 'threed_secure';
            $authentication->status = $additionalInformation['authentication']['trans_status'];

            $threeDSecure = new stdClass();
            $threeDSecure->mpi = 'pagarme';
            $threeDSecure->transactionId = $additionalInformation['authentication']['tds_server_trans_id'];

            $authentication->threeDSecure = $threeDSecure;
            $newPaymentData->authentication = $authentication;
        }

        $amount = $this->getGrandTotal() - $this->getBaseTaxAmount();
        $amount = number_format($amount, 2, '.', '');
        $amount = str_replace('.', '', $amount ?? '');
        $amount = str_replace(',', '', $amount ?? '');

        $newPaymentData->amount = $amount;

        if ($additionalInformation['cc_buyer_checkbox']) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'cc',
                $additionalInformation
            );
        }

        return $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeTwoCreditCard($additionalInformation, &$paymentData, $payment)
    {
        $indexes = ['first', 'second'];
        foreach ($indexes as $index) {
            $identifier = null;
            $customerId = null;

            $brand = null;
            try {
                $brand = strtolower($additionalInformation["cc_type_{$index}"]);
            } catch (\Throwable $e) {
            }

            if (isset($additionalInformation["cc_token_credit_card_{$index}"])) {
                $identifier = $additionalInformation["cc_token_credit_card_{$index}"];
            }

            if (
                !empty($additionalInformation["cc_saved_card_{$index}"]) &&
                $additionalInformation["cc_saved_card_{$index}"] !== null
            ) {
                $identifier = null;
            }

            if ($identifier === null) {
                $objectManager = ObjectManager::getInstance();
                $cardRepo = $objectManager->get(CardsRepository::class);
                $cardId = $additionalInformation["cc_saved_card_{$index}"];
                $card = $cardRepo->getById($cardId);

                $identifier = $card->getCardToken();
                $customerId = $card->getCardId();
            }

            $newPaymentData = new stdClass();
            $newPaymentData->customerId = $customerId;
            $newPaymentData->identifier = $identifier;
            $newPaymentData->brand = $brand;
            $newPaymentData->installments = $additionalInformation["cc_installments_{$index}"];
            $newPaymentData->customer = $this->extractMultibuyerData(
                'cc',
                $additionalInformation,
                $index
            );

            $amount = $this->moneyService->removeSeparators(
                $additionalInformation["cc_{$index}_card_amount"]
            );

            $newPaymentData->amount = $this->moneyService->floatToCents($amount / 100);
            $newPaymentData->saveOnSuccess =
                isset($additionalInformation["cc_savecard_{$index}"]) &&
                $additionalInformation["cc_savecard_{$index}"] === '1';

            $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
            if (!isset($paymentData[$creditCardDataIndex])) {
                $paymentData[$creditCardDataIndex] = [];
            }
            $paymentData[$creditCardDataIndex][] = $newPaymentData;
        }
    }

    private function extractMultibuyerData(
        $prefix,
        $additionalInformation,
        $index = null
    ) {
        $index = $index !== null ? '_' . $index : null;

        if (
            !isset($additionalInformation["{$prefix}_buyer_checkbox{$index}"]) ||
            $additionalInformation["{$prefix}_buyer_checkbox{$index}"] !== "1"
        ) {
            return null;
        }

        $fields = [
            "{$prefix}_buyer_name{$index}" => "name",
            "{$prefix}_buyer_email{$index}" => "email",
            "{$prefix}_buyer_document{$index}" => "document",
            "{$prefix}_buyer_street_title{$index}" => "street",
            "{$prefix}_buyer_street_number{$index}" => "number",
            "{$prefix}_buyer_neighborhood{$index}" => "neighborhood",
            "{$prefix}_buyer_street_complement{$index}" => "complement",
            "{$prefix}_buyer_city{$index}" => "city",
            "{$prefix}_buyer_state{$index}" => "state",
            "{$prefix}_buyer_zipcode{$index}" => "zipCode",
            "{$prefix}_buyer_home_phone{$index}" => "homePhone",
            "{$prefix}_buyer_mobile_phone{$index}" => "mobilePhone"
        ];

        $multibuyer = new stdClass();

        foreach ($fields as $key => $attribute) {
            $value = $additionalInformation[$key];

            if (($attribute === 'document' || $attribute === 'zipCode') && !is_null($value)) {
                $value = preg_replace(
                    '/\D/',
                    '',
                    $value
                );
            }

            $multibuyer->$attribute = $value;
        }

        return $multibuyer;
    }

    private function extractPaymentDataFromPagarmeBilletCreditcard(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $identifier = null;
        $customerId = null;

        $brand = null;
        try {
            $brand = strtolower($additionalInformation['cc_type']);
        } catch (\Throwable $e) {
        }

        if (isset($additionalInformation['cc_token_credit_card'])) {
            $identifier = $additionalInformation['cc_token_credit_card'];
        }

        if (
            !empty($additionalInformation['cc_saved_card']) &&
            $additionalInformation['cc_saved_card'] !== null
        ) {
            $identifier = null;
        }

        if ($identifier === null) {
            $objectManager = ObjectManager::getInstance();
            $cardRepo = $objectManager->get(CardsRepository::class);
            $cardId = $additionalInformation['cc_saved_card'];
            $card = $cardRepo->getById($cardId);

            $identifier = $card->getCardToken();
            $customerId = $card->getCardId();
        }

        $newPaymentData = new stdClass();
        $newPaymentData->identifier = $identifier;
        $newPaymentData->customerId = $customerId;
        $newPaymentData->brand = $brand;
        $newPaymentData->installments = $additionalInformation['cc_installments'];

        $newPaymentData->saveOnSuccess =
            isset($additionalInformation["cc_savecard"]) &&
            $additionalInformation["cc_savecard"] === '1';

        $amount = str_replace(
            ['.', ','],
            "",
            $additionalInformation["cc_cc_amount"] ?? ''
        );
        $newPaymentData->amount = $this->moneyService->floatToCents($amount / 100);

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }

        $newPaymentData->customer = $this->extractMultibuyerData(
            'cc',
            $additionalInformation
        );

        $paymentData[$creditCardDataIndex][] = $newPaymentData;

        //boleto

        $newPaymentData = new stdClass();

        $amount = str_replace(
            ['.', ','],
            "",
            $additionalInformation["cc_billet_amount"] ?? ''
        );

        $newPaymentData->amount =
            $this->moneyService->floatToCents($amount / 100);

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        $newPaymentData->customer = $this->extractMultibuyerData(
            'billet',
            $additionalInformation
        );

        $paymentData[$boletoDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeBillet(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = new stdClass();
        $newPaymentData->amount =
            $this->moneyService->floatToCents($this->platformOrder->getGrandTotal());
        $moduleConfiguration = MPSetup::getModuleConfiguration();
        $newPaymentData->instructions = $moduleConfiguration->getBoletoInstructions();
        $bankConfig = new Bank();
        $bankNumber = $bankConfig->getBankNumber(MPSetup::getModuleConfiguration()->getBoletoBankCode());
        if ($bankNumber) {
            $newPaymentData->bank = $bankNumber;
        }
        $expirationDate = new \DateTime();
        $days = MPSetup::getModuleConfiguration()->getBoletoDueDays();
        if ($days) {
            $expirationDate->modify("+{$days} day");
        }
        $newPaymentData->due_at = $expirationDate->format('c');

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        if ($additionalInformation['billet_buyer_checkbox']) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'billet',
                $additionalInformation
            );
        }

        $paymentData[$boletoDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmePix(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = new stdClass();
        $newPaymentData->amount =
            $this->moneyService->floatToCents($this->platformOrder->getGrandTotal());

        $pixDataIndex = PixPayment::getBaseCode();
        if (!isset($paymentData[$pixDataIndex])) {
            $paymentData[$pixDataIndex] = [];
        }

        if (!empty($additionalInformation['pix_buyer_checkbox'])) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'pix',
                $additionalInformation
            );
        }

        $paymentData[$pixDataIndex][] = $newPaymentData;
    }

    public function getShipping()
    {
        /** @var Shipping $shipping */
        $shipping = null;
        $quote = $this->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $platformShipping */
        $platformShipping = $quote->getShippingAddress();

        $shippingMethod = $platformShipping->getShippingMethod();
        if ($shippingMethod === null) { //this is a order without a shipping.
            return null;
        }

        $shipping = new Shipping();

        $shipping->setAmount(
            $this->moneyService->floatToCents($platformShipping->getShippingAmount())
        );
        $shipping->setDescription($platformShipping->getShippingDescription());
        $shipping->setRecipientName($platformShipping->getName());

        $telephone = $platformShipping->getTelephone();
        $phone = new Phone($telephone);

        $shipping->setRecipientPhone($phone);

        $address = $this->getAddress($platformShipping);
        $shipping->setAddress($address);

        return $shipping;
    }

    protected function getAddress($platformAddress)
    {
        $address = new Address();
        $addressAttributes =
            MPSetup::getModuleConfiguration()->getAddressAttributes();

        $addressAttributes = json_decode(json_encode($addressAttributes), true);
        $allStreetLines = $platformAddress->getStreet();

        $this->validateAddress($allStreetLines);
        $this->validateAddressConfiguration($addressAttributes);

        if (count($allStreetLines) < 4) {
            $addressAttributes['neighborhood'] = "street_3";
            $addressAttributes['complement'] = "street_4";
        }

        foreach ($addressAttributes as $attribute => $value) {
            $value = $value === null ? 1 : $value;

            $street = explode("_", $value ?? '');
            if (count($street) > 1) {
                $value = intval($street[1]) - 1;
            }

            $setter = 'set' . ucfirst($attribute);

            if (!isset($allStreetLines[$value])) {
                $address->$setter('');
                continue;
            }

            $address->$setter($platformAddress->getStreet()[$value]);
        }

        $address->setCity($platformAddress->getCity());
        $address->setCountry($platformAddress->getCountryId());
        $address->setZipCode($platformAddress->getPostcode());

        $_regionFactory = ObjectManager::getInstance()->get('Magento\Directory\Model\RegionFactory');
        $regionId = $platformAddress->getRegionId();

        if (is_numeric($regionId)) {
            $shipperRegion = $_regionFactory->create()->load($regionId);
            if ($shipperRegion->getId()) {
                $address->setState($shipperRegion->getCode());
            }
        }

        return $address;
    }

    protected function validateAddress($allStreetLines)
    {
        if (
            !is_array($allStreetLines) ||
            count($allStreetLines) < 3
        ) {
            $message = "Invalid address. Please fill the street lines and try again.";
            $ExceptionMessage = $this->i18n->getDashboard($message);

            $exception = new \Exception($ExceptionMessage);
            $log = new LogService('Order', true);
            $log->exception($exception);

            throw $exception;
        }
    }

    protected function validateAddressConfiguration($addressAttributes)
    {
        $arrayFiltered = array_filter($addressAttributes);
        if (empty($arrayFiltered)) {
            $message = "Invalid address configuration. Please fill the address configuration on admin panel.";
            $ExceptionMessage = $this->i18n->getDashboard($message);
            $exception = new \Exception($ExceptionMessage);

            $log = new LogService('Order', true);
            $log->exception($exception);


            throw $exception;
        }
    }

    public function getTotalCanceled()
    {
        return $this->platformOrder->getTotalCanceled();
    }

    public function handleSplitOrder()
    {
        $webkullHelper = new WebkulHelper();

        if (!$webkullHelper->isEnabled()) {
            return null;
        }

        $splitDataFromOrder = $webkullHelper->getSplitDataFromOrder($this);

        if (!$splitDataFromOrder) {
            return null;
        }

        $splitData = new Split();
        $splitData->setSellersData($splitDataFromOrder['sellers']);
        $splitData->setMarketplaceData($splitDataFromOrder['marketplace']);

        return $splitData;
    }
}
