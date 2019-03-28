<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Service\OrderService;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;
use MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\Config as ConfigCreditCard;
use MundiPagg\MundiPagg\Helper\Logger;
use MundiPagg\MundiPagg\Model\MundiPaggConfigProvider;
use Magento\Framework\App\ObjectManager;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /** session factory */
    protected $_session;

    /** @var CustomerFactoryInterface */
    protected $customerFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \MundiPagg\MundiPagg\Helper\Logger
     */
    protected $logger;

    /**
     * \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * \MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\Config
     */
    protected $configCreditCard;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \MundiPagg\MundiPagg\Helper\Logger $logger
     * @param Api $api
     */
    public function __construct(
        Session $checkoutSession,
        Logger $logger,
        OrderService $orderService,
        CustomerFactory $customerFactory,
        SessionFactory $sessionFactory,
        CustomerRepositoryInterface $customerRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        ConfigCreditCard $configCreditCard
    ) {
        $this->setCheckoutSession($checkoutSession);
        $this->setLogger($logger);
        $this->setOrderService($orderService);
        $this->setTransaction($transaction);
        $this->setInvoiceService($invoiceService);
        $this->setInvoiceSender($invoiceSender);
        $this->customerFactory = $customerFactory;
        $this->sessionFactory = $sessionFactory;
        $this->customerRepository = $customerRepository;
        $this->configCreditCard = $configCreditCard;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleIsEnable()) {
            return $this;
        }

        $event = $observer->getEvent();
        $order = $event->getOrder();
        $payment = $order->getPayment();

        if ($payment->getMethod() != 'mundipagg_creditcard') {
            return $this;
        }

        if ( $order->canInvoice() && $this->configCreditCard->getPaymentAction() == 'authorize_capture' ) {
            $result = $this->createInvoice($order);
            $this->getLogger()->logger($result);
        }

        return $this;
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $mundipaggProvider = $objectManager->get(MundiPaggConfigProvider::class);

        return $mundipaggProvider->getModuleStatus();
    }

    /**
     * @param Order $order
     * @return $invoice
     */
    public function createInvoice($order)
    {
        $detourOn = [
            'mundipagg_creditcard',
            'mundipagg_billet',
            'mundipagg_two_creditcard'
        ];

        $payment = $order->getPayment();
        if (in_array($payment->getMethod(), $detourOn)) {
            return true;
        }

        $payment
            ->setIsTransactionClosed(true)
            ->registerCaptureNotification(
                $order->getGrandTotal(),
                true
            );
        $order->save();
        // notify customer
        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$order->getEmailSent()) {
            $order->addStatusHistoryComment(
                    'MP - ' .
                    __(
                        'Notified customer about invoice #%1.',
                        $invoice->getIncrementId()
                    )
                )->setIsCustomerNotified(true)
                ->save();
        }

        return true;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     *
     * @return self
     */
    public function setCheckoutSession(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;

        return $this;
    }

    /**
     * @return \MundiPagg\MundiPagg\Helper\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \MundiPagg\MundiPagg\Helper\Logger $logger
     *
     * @return self
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @param mixed $orderService
     *
     * @return self
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceService()
    {
        return $this->invoiceService;
    }

    /**
     * @param mixed $invoiceService
     *
     * @return self
     */
    public function setInvoiceService($invoiceService)
    {
        $this->invoiceService = $invoiceService;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param mixed $transaction
     *
     * @return self
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceSender()
    {
        return $this->invoiceSender;
    }

    /**
     * @param mixed $invoiceSender
     *
     * @return self
     */
    public function setInvoiceSender($invoiceSender)
    {
        $this->invoiceSender = $invoiceSender;

        return $this;
    }
}
