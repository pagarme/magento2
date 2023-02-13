<?php
    namespace Pagarme\Pagarme\Helper\Payment;

    use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
    use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
    use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
    use Pagarme\Core\Kernel\Repositories\OrderRepository;
    use Pagarme\Core\Recurrence\Repositories\ChargeRepository as SubscriptionChargeRepository;

    class Billet {
        
        public function getBilletUrl($info)
        {
            $method = $info->getMethod();
            if (strpos($method, "pagarme_billet") === false) {
                return;
            }
            $boletoUrl = $info->getAdditionalInformation('billet_url');
            Magento2CoreSetup::bootstrap();
            $boletoUrl = $this->getBoletoLinkFromOrder($info);

            if (!$boletoUrl) {
                $boletoUrl = $this->getBoletoLinkFromSubscription($info);
            }
    
            return $boletoUrl;
        }

        private function getBoletoLinkFromOrder($info)
        {
            $lastTransId = $info->getLastTransId();
            $orderId = null;
            if ($lastTransId) {
                $orderId = substr($lastTransId, 0, 19);
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