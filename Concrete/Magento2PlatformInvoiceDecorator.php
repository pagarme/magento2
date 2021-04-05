<?php

namespace Pagarme\Pagarme\Concrete;

use JsonSerializable;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Invoice;
use Pagarme\Core\Kernel\Abstractions\AbstractInvoiceDecorator;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\InvoiceState;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;


class Magento2PlatformInvoiceDecorator extends AbstractInvoiceDecorator implements
    JsonSerializable
{
    public function save()
    {
        $this->platformInvoice->save();
    }

    public function loadByIncrementId($incrementId)
    {
        // TODO: Implement loadByIncrementId() method.
    }

    public function getIncrementId()
    {
        return $this->platformInvoice->getIncrementId();
    }

    public function prepareFor(PlatformOrderInterface $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $invoiceService = ObjectManager::getInstance()->get('Magento\Sales\Model\Service\InvoiceService');
        $this->platformInvoice = $invoiceService->prepareInvoice($platformOrder);
    }

    public function createFor(PlatformOrderInterface $order)
    {
        $logService = new LogService(
            'Invoice',
            true
        );

        $logService->info("Preparing invoice for order #{$order->getIncrementId()}.");
        $this->prepareFor($order);
        $this->platformInvoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $this->platformInvoice->register();

        $grandTotal = $order->getTotalPaidFromCharges();
        $this->platformInvoice->setBaseGrandTotal($grandTotal);
        $this->platformInvoice->setGrandTotal($grandTotal);

        $orderGrandTotal = $order->getGrandTotal();
        $moneyService = new MoneyService();
        $orderGrandTotal = $moneyService->floatToCents($orderGrandTotal);
        $orderGrandTotal = $moneyService->centsToFloat($orderGrandTotal);

        if ($grandTotal !== $orderGrandTotal) {
            $i18n = new LocalizationService();
            $comment = $i18n->getDashboard(
                "Different paid amount for this invoice. Paid value: %.2f",
                $grandTotal
            );

            $this->addComment($comment);
        }

        $this->save();
        $logService->info("Invoice saved #{$this->getIncrementId()}");

        $transactionSave = ObjectManager::getInstance()->get('Magento\Framework\DB\Transaction');
        $transactionSave->addObject(
            $this->platformInvoice
        )->addObject(
            $this->platformInvoice->getOrder()
        );
        $transactionSave->save();

        $objectManager = ObjectManager::getInstance();
        $invoiceSender = $objectManager->get(InvoiceSender::class);
        $logService->info("Sending invoice #{$this->getIncrementId()}");
        $invoiceSender->send($this->platformInvoice);
        $logService->info("Invoice sent #{$this->getIncrementId()}");
    }

    public function setState(InvoiceState $state)
    {
        $mageState = Invoice::STATE_PAID;

        if ($state->equals(InvoiceState::canceled())) {
            $mageState = Invoice::STATE_CANCELED;
        }

        $this->platformInvoice->setState($mageState);
    }

    public function canRefund()
    {
        return $this->platformInvoice->canRefund();
    }

    public function isCanceled()
    {
        return $this->platformInvoice->isCanceled();
    }

    private function createInvoice($order)
    {
        $objectManager = ObjectManager::getInstance();
        $invoiceService = $objectManager->get(InvoiceService::class);
        $transaction = $objectManager->get(Transaction::class);
        $invoiceSender = $objectManager->get(InvoiceSender::class);

        $invoice = $invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->save();
        $transactionSave = $transaction->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();

        //Remove comments if you need to send e-mail here until we don't create
        //a class for e-mail sending.
        //$invoiceSender->send($invoice);

        $order->addStatusHistoryComment(
            'PGM - ' .
            __('Notified customer about invoice #%1.', $invoice->getIncrementId())
        )
            ->setIsCustomerNotified(true)
            ->save();

        $payment = $order->getPayment();
        $payment
            ->setIsTransactionClosed(true)
            ->registerCaptureNotification(
                $order->getGrandTotal(),
                true
            );

        $order->setState('processing')->setStatus('processing');
        $order->save();

        return $invoice;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->platformInvoice->getData();
    }

    protected function addMPComment($comment)
    {
        $this->platformInvoice->addComment($comment);
    }
}
