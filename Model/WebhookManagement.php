<?php

namespace MundiPagg\MundiPagg\Model;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as M2WebApiException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Service\OrderService;
use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Webhook\Services\WebhookReceiverService;
use MundiPagg\MundiPagg\Api\WebhookManagementInterface;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\Logger;

class WebhookManagement implements WebhookManagementInterface
{
    /**
     * \MundiPagg\MundiPagg\Model\ChargesFactory
     */
    protected $chargesFactory;

    /**
     * \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

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
     * \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \MundiPagg\MundiPagg\Helper\Logger
     */
    protected $logger;

    public function __construct(
        Order $orderFactory,
        ChargesFactory $chargesFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        OrderService $orderService,
        Logger $logger
    ) {
        $this->setOrderFactory($orderFactory);
        $this->setChargesFactory($chargesFactory);
        $this->setTransaction($transaction);
        $this->setInvoiceService($invoiceService);
        $this->setOrderRepository($orderRepository);
        $this->setInvoiceSender($invoiceSender);
        $this->setCreditmemoFactory($creditmemoFactory);
        $this->setCreditmemoService($creditmemoService);
        $this->setOrderService($orderService);
        $this->setLogger($logger);
    }


    /**
     * @api
     * @param mixed $id
     * @param mixed $type
     * @param mixed $data
     * @return array|bool
     */
    public function save($id, $type, $data)
    {
        try {
            Magento2CoreSetup::bootstrap();

            $this->getLogger()->logger($data);
            //log webhook received.

            $postData = new \stdClass();
            $postData->id = $id;
            $postData->type = $type;
            $postData->data = $data;

            $webhookReceiverService = new WebhookReceiverService();
            return $webhookReceiverService->handle($postData);
        }catch(AbstractMundipaggCoreException $e){
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                $e->getCode()
            );
        }

        //@deprecated code.
        $statusOrder = $data['status'];

        $isCharge = 'ch_';
        if (substr($data['id'], 0, 3) == $isCharge) {
            $charges[] = $data;
        } else {
            $charges = $data['charges'];
        }

        foreach ($charges as $charge) {
            //$result[] = $this->saveCharge($charge);
            $result[] = $this->saveOrder($charge);
        }

        $result[] = ["success" => 200];

        return $result;
    }

    /**
     * @deprecated
     */
    protected function saveOrder($charge)
    {
        $result[] = ["order" => "here"];
        $chageMagento = $this->getChargeMagentoByChargeId($charge);
        $orderId = $this->getOrderFactory()->loadByIncrementId($chageMagento->getOrderId());
        $order = $this->getOrderRepository()->get($orderId->getId());

        if ($order->canInvoice() && $charge['status'] == 'paid') {
            $invoice = $this->createInvoice($order);
            $result[] = [
                "order" => "canInvoice",
                "invoice" => $invoice,
            ];
        }

        if ($order->canCancel() && $charge['status'] == 'payment_failed') {
            $cancel = $this->cancelOrder($order);
            $result[] = [
                "order" => "canCancel",
                "cancel" => $cancel,
            ];
        }

        if ($order->canCancel() && $charge['status'] == 'canceled') {
            $cancel = $this->cancelOrder($order);
            $result[] = [
                "order" => "canCancel",
                "cancel" => $cancel,
            ];
        }

        if ($order->canCreditmemo() && $charge['status'] == 'refunded') {
            $creditmemo = $this->createCreditMemo($order);
            $result[] = [
                "order" => "canCreditmemo",
                "creditmemo" => $creditmemo,
            ];
        }

        return $result;
    }

    protected function getChargeMagentoByChargeId($charge)
    {
        $chargeId = $charge['id'];

        $model = $this->getChargesFactory();
        $chargeCollection = $model->getCollection()->addFieldToFilter('charge_id', ['eq' => $chargeId])->getFirstItem();

        return $chargeCollection;
    }

    protected function cancelOrder($order)
    {
        $cancel = $this->getOrderService()->cancel($order->getId());

        return $cancel;
    }

    protected function createCreditMemo($order)
    {
        $creditmemo = $this->getCreditmemoFactory()->createByOrder($order);

        $creditmemoServiceRefund = $this->getCreditmemoService()->refund($creditmemo, true);

        return $creditmemoServiceRefund->getData();
    }

    /** @deprecated  */
    protected function createInvoice($order)
    {
        $invoice = $this->getInvoiceService()->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->getTransaction()->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();
        $this->getInvoiceSender()->send($invoice);

        $order->addStatusHistoryComment(
            __('Notified customer about invoice #%1.', $invoice->getIncrementId())
        )
        ->setIsCustomerNotified(true)
        ->save();

        $order->setState('processing')->setStatus('processing');
        $order->save();

        return $invoice->getData();
    }

    protected function saveCharge($charge)
    {
        $chargeId = $charge['id'];
        $statusCharge = $charge['status'];
        $amount = $charge['amount'];

        $chargeCollection = $this->getChargeMagentoByChargeId($charge);

        if ($statusCharge == 'paid') {
            $chargeCollection->setStatus($statusCharge)->setPaidAmount($amount)->setUpdatedAt(date("Y-m-d H:i:s"));
        }

        if ($statusCharge == 'refunded') {
            $chargeCollection->setStatus($statusCharge)->setRefundedAmount($amount)->setUpdatedAt(date("Y-m-d H:i:s"));
        }

        if ($statusCharge == 'payment_failed') {
            $chargeCollection->setStatus($statusCharge)->setUpdatedAt(date("Y-m-d H:i:s"));
        }

        try {
            $this->getLogger()->logger($chargeCollection);
            $chargeCollection->save();
        } catch (\Exception $e) {
            $this->getLogger()->logger($e);
            return $e->getMessage();
        }

        $result[] = [
            "charge_id" => $chargeId,
            "status" => $statusCharge,
            "amount" => $amount,
        ];
        $result[] = $chargeCollection->getData();

        return $result;
    }

    protected function getChargesFactory()
    {
        return $this->chargesFactory->create();
    }

    /**
     * @param mixed $chargesFactory
     *
     * @return self
     */
    public function setChargesFactory($chargesFactory)
    {
        $this->chargesFactory = $chargesFactory;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderFactory()
    {
        return $this->orderFactory;
    }

    /**
     * @param mixed $orderFactory
     *
     * @return self
     */
    public function setOrderFactory($orderFactory)
    {
        $this->orderFactory = $orderFactory;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderRepository()
    {
        return $this->orderRepository;
    }

    /**
     * @param mixed $orderRepository
     *
     * @return self
     */
    public function setOrderRepository($orderRepository)
    {
        $this->orderRepository = $orderRepository;

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

    /**
     * @return mixed
     */
    public function getCreditmemoFactory()
    {
        return $this->creditmemoFactory;
    }

    /**
     * @param mixed $creditmemoFactory
     *
     * @return self
     */
    public function setCreditmemoFactory($creditmemoFactory)
    {
        $this->creditmemoFactory = $creditmemoFactory;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreditmemoService()
    {
        return $this->creditmemoService;
    }

    /**
     * @param mixed $creditmemoService
     *
     * @return self
     */
    public function setCreditmemoService($creditmemoService)
    {
        $this->creditmemoService = $creditmemoService;

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
    public function setLogger(\MundiPagg\MundiPagg\Helper\Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
