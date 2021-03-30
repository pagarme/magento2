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


use Magento\Payment\Block\Info;
use Magento\Framework\DataObject;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository as SubscriptionChargeRepository;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class Billet extends Info
{
    const TEMPLATE = 'Pagarme_Pagarme::info/billet.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getBilletUrl()
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    public function getBilletUrl()
    {
        $method = $this->getInfo()->getMethod();

        if (strpos($method, "pagarme_billet") === false) {
            return;
        }

        $boletoUrl =  $this->getInfo()->getAdditionalInformation('billet_url');

        Magento2CoreSetup::bootstrap();
        $info = $this->getInfo();

        $boletoUrl = $this->getBoletoLinkFromOrder($info);

        if (!$boletoUrl) {
            $boletoUrl = $this->getBoletoLinkFromSubscription($info);
        }

        return $boletoUrl;
    }

    private function getBoletoLinkFromOrder($info)
    {
        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        if (!$orderId) {
            return null;
        }

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByPagarmeId(new OrderId($orderId));

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
                $subscription->getPagarmeId()->getValue()
            );

        $charge = $chargeRepository->findBySubscriptionId($subscriptionId);

        if (!empty($charge[0])) {
            return $charge[0]->getBoletoLink();
        }
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function getTransactionInfo()
    {
        Magento2CoreSetup::bootstrap();
        $orderService = new OrderService();

        $orderEntityId = $this->getInfo()->getOrder()->getIncrementId();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();

        if ($orderPagarmeId === null) {
            return [];
        }

        /**
         * @var \Pagarme\Core\Kernel\Aggregates\Order orderObject
         */
        $orderObject = $orderService->getOrderByPagarmeId(new OrderId($orderPagarmeId));
        return $orderObject->getCharges()[0]->getLastTransaction();
    }
}
