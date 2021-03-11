<?php
/**
 * Class Billet
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Payment\Info;

use Magento\Payment\Block\Info\Cc;

use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository as SubscriptionChargeRepository;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class BilletCreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/billetCreditCard.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getInfo()->getAdditionalInformation('billet_url')
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    public function _construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->setTemplate(self::TEMPLATE);
    }

    public function getCcType()
    {
        return $this->getCcTypeName();
    }

    public function getCardNumber()
    {
        return '**** **** **** ' . $this->getInfo()->getCcLast4();
    }

    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    public function getBilletUrl()
    {
        $method = $this->getInfo()->getMethod();

        if (strpos($method, "pagarme_billet") === false) {
            return;
        }

        $info = $this->getInfo();
        $boletoUrl = $this->getBoletoLinkFromOrder($info);

        if (!$boletoUrl) {
            $boletoUrl = $this->getBoletoLinkFromSubscription($info);
        }

        Magento2CoreSetup::bootstrap();

        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByMundipaggId(new OrderId($orderId));

        if ($order !== null) {
            $charges = $order->getCharges();
            foreach ($charges as $charge) {
                $transaction = $charge->getLastTransaction();
                $savedBoletoUrl = $transaction->getBoletoUrl();
                if ($savedBoletoUrl !== null) {
                    $boletoUrl = $savedBoletoUrl;
                }
            }
        }

        return $boletoUrl;
    }

    public function getCcAmount()
    {
        return $this->getInfo()->getAdditionalInformation('cc_cc_amount');
    }

    public function getCcAmountWithTax()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_cc_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_cc_tax_amount');
    }

    public function getBilletAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_billet_amount');
    }

    private function getBoletoLinkFromOrder($info)
    {
        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        if (!$orderId) {
            return null;
        }

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByMundipaggId(new OrderId($orderId));

        if ($order !== null) {
            $charges = $order->getCharges();
            foreach ($charges as $charge) {
                $transaction = $charge->getLastTransaction();
                $savedBoletoUrl = $transaction->getBoletoUrl();
                if ($savedBoletoUrl !== null) {
                    $boletoUrl = $savedBoletoUrl;
                }
            }
        }

        return $boletoUrl;
    }

    private function getBoletoLinkFromSubscription($info)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $subscription = $subscriptionRepository->findByCode($info->getOrder()->getIncrementId());

        if (!$subscription) {
            return null;
        }

        $chargeRepository = new SubscriptionChargeRepository();
        $subscriptionId =
            new SubscriptionId(
                $subscription->getMundipaggId()->getValue()
            );

        $charge = $chargeRepository->findBySubscriptionId($subscriptionId);

        if (!empty($charge[0])) {
            return $charge[0]->getBoletoLink();
        }
    }

    public function getTransactionInfo()
    {
        $orderService = new OrderService();

        $orderEntityId = $this->getInfo()->getOrder()->getIncrementId();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();
        if ($orderPagarmeId === null) {
            return [];
        }

        /**
         * @var \Mundipagg\Core\Kernel\Aggregates\Order orderObject
         */
        $orderObject = $orderService->getOrderByMundiPaggId(new OrderId($orderPagarmeId));

        $lastTransaction = $orderObject->getCharges()[0]->getLastTransaction();
        $secondLastTransaction = $orderObject->getCharges()[1]->getLastTransaction();

        $transactionList = [];
        foreach ([$lastTransaction, $secondLastTransaction] as $item) {
            if ($item->getAcquirerNsu() != 0) {
                $transactionList['creditCard'] =
                    array_merge(
                        $orderObject->getCharges()[0]->getAcquirerTidCapturedAndAutorize(),
                        ['tid' => $this->getTid($orderObject->getCharges()[0])]
                    );

                continue;
            }

            $transactionList['billet'] = $item;
        }

        return $transactionList;
    }

    private function getTid(Charge $charge)
    {
        $transaction = $charge->getLastTransaction();

        $tid = null;
        if ($transaction !== null) {
            $tid = $transaction->getAcquirerTid();
        }

        return $tid;
    }
}
