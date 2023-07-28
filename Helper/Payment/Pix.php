<?php

namespace Pagarme\Pagarme\Helper\Payment;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Services\OrderService;

class Pix
{
    private $qrCodeUrl;
    private $qrCode;

    /**
     * @return $this
     */
    public function getInfo($info)
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
        $orderService = new OrderService();
        $qrCodeData = $orderService->getPixQrCodeInfoFromOrder(new OrderId($orderId));
        $this->setQrCode($qrCodeData['qr_code']);
        $this->setQrCodeUrl($qrCodeData['qr_code_url']);
        return $this;
    }


    /**
     * @return string
     */
    public function getQrCodeUrl()
    {
        return $this->qrCodeUrl ?? '';
    }

    /**
     * @return string
     */
    public function getQrCode()
    {
        return $this->qrCode ?? '';
    }


    private function setQrCodeUrl($qrCodeUrl)
    {
        $this->qrCodeUrl = $qrCodeUrl;
    }

    private function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;
    }
}
