<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Mundipagg\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Mundipagg\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

class Magento2PlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var Order */
    protected $platformOrder;
    /**
     * @var Order
     */
    private $orderFactory;

    public function __construct()
    {
        $objectManager = ObjectManager::getInstance();
        $this->orderFactory = $objectManager->get('Magento\Sales\Model\Order');
    }

    public function save()
    {
        /*
         * @fixme Saving order this way in magento2 is deprecated.
         *        Find out how to fix this.
         */
        $this->platformOrder->save();
    }

    public function setState(OrderState $state)
    {
       $stringState = $state->getState();
       $this->platformOrder->setState($stringState);
    }


    /**
     * @return OrderState;
     */
    public function getState()
    {
        $baseState = explode('_', $this->getPlatformOrder()->getState());
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

    public function setStatus(OrderStatus $status)
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

    protected function addMPHistoryComment($message)
    {
        $historyMethod = 'addCommentToStatusHistory';
        if (!method_exists($this->platformOrder, $historyMethod)) {
            $historyMethod = 'addStatusHistoryComment';
        }
        $this->platformOrder->$historyMethod($message);
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

    /** @return OrderId */
    public function getMundipaggId()
    {
        $orderId = $this->platformOrder->getPayment()->getLastTransId();

        if (empty($orderId)) {
            return null;
        }

        return new OrderId($this->platformOrder->getPayment()->getLastTransId());
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
}