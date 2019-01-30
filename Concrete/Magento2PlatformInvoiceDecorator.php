<?php

namespace MundiPagg\MundiPagg\Concrete;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Invoice;
use Mundipagg\Core\Kernel\Abstractions\AbstractInvoiceDecorator;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;


class Magento2PlatformInvoiceDecorator extends AbstractInvoiceDecorator
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
        //$this->platformInvoice = $this->createInvoice($order->getPlatformOrder());

        //return;

        //@deprecated code
        $this->prepareFor($order);
        $this->platformInvoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $this->platformInvoice->register();
        $this->save();
        $transactionSave = ObjectManager::getInstance()->get('Magento\Framework\DB\Transaction');
        $transactionSave->addObject(
            $this->platformInvoice
        )->addObject(
            $this->platformInvoice->getOrder()
        );
        $transactionSave->save();
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
            'MP - ' .
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
}