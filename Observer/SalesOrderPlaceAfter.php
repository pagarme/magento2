<?php

namespace Pagarme\Pagarme\Observer;

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
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config as ConfigCreditCard;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
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
     * \Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config
     */
    protected $configCreditCard;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Api $api
     */
    public function __construct(
        Session $checkoutSession,
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

        if ($payment->getMethod() != 'pagarme_creditcard') {
            return $this;
        }

        if ( $order->canInvoice() && $this->configCreditCard->getPaymentAction() == 'authorize_capture' ) {
            $result = $this->createInvoice($order);
        }

        return $this;
    }

    public function moduleIsEnable()
    {
        $objectManager = ObjectManager::getInstance();
        $pagarmeProvider = $objectManager->get(PagarmeConfigProvider::class);

        return $pagarmeProvider->getModuleStatus();
    }

    /**
     * @param Order $order
     * @return $invoice
     */
    public function createInvoice($order)
    {
        return true;

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
                    'PGM - ' .
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
