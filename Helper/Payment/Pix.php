<?php
    namespace Pagarme\Pagarme\Helper\Payment;

    use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
    use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
    use Pagarme\Core\Payment\Services\OrderService;

    class Pix {

        public function getQrCode($info)
        {
            $orderId = null;
            $method = $info->getMethod();
            if (strpos($method, "pagarme_pix") === false) {
                return null;
            }

            $lastTransId = $info->getLastTransId();
            if ($lastTransId) {
                $orderId = substr($lastTransId, 0, 19);
            }

            Magento2CoreSetup::bootstrap();
            $orderService= new OrderService();
            return $orderService->getPixQrCodeInfoFromOrder(new OrderId($orderId));
        }
    }