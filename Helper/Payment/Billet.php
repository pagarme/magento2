<?php
    namespace Pagarme\Pagarme\Helper\Payment;

    use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
    use Pagarme\Pagarme\Helper\OrderHelper;
    use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
    use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
    use Pagarme\Core\Kernel\Repositories\OrderRepository;
    use Pagarme\Core\Recurrence\Repositories\ChargeRepository as SubscriptionChargeRepository;
    use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;

    class Billet {

        public function getBilletUrl($info, $transaction = null)
        {
            $method = $info->getMethod();
            if (strpos($method, "pagarme_billet") === false) {
                return;
            }

            Magento2CoreSetup::bootstrap();
            $boletoUrl = $this->getBoletoLinkFromOrder($info, $transaction);

            if (!$boletoUrl) {
                $boletoUrl = $this->getBoletoLinkFromSubscription($info);
            }

            return $boletoUrl;
        }

        private function getBoletoLinkFromOrder($info, $transaction = null)
        {
            $orderId = OrderHelper::getPagarmeOrderId($info);

            if (!$orderId && !is_null($transaction)) {
                return $transaction->getBoletoUrl();
            }

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
                return $charge[0]->getBoletoUrl();
            }

            return null;
        }
    }
