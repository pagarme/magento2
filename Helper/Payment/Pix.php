<?php

namespace Pagarme\Pagarme\Helper\Payment;

use Exception;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Services\OrderService;
use Pagarme\Pagarme\Helper\OrderHelper;

class Pix
{
    const LOGO_URL = "Pagarme_Pagarme::images/logo-pix.svg";

    private $qrCodeUrl;
    private $qrCode;

    /**
     * @return $this
     * @throws Exception
     */
    public function getInfo($info, $transaction = null)
    {
        $orderId = null;
        $method = $info->getMethod();
        if (strpos($method, "pagarme_pix") === false) {
            return null;
        }

        $orderId = OrderHelper::getPagarmeOrderId($info);

        if (!$orderId && !is_null($transaction)) {
            $this->setQrCode($transaction->getPostData()->qr_code);
            $this->setQrCodeUrl($transaction->getPostData()->qr_code_url);
            return $this;
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
