<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\DataObject;
use Pagarme\Pagarme\Api\KycLinkResponseInterface;

class KycLinkResponse extends DataObject implements KycLinkResponseInterface
{
    public function getUrl()
    {
        return $this->_getData(self::DATA_URL);
    }

    public function getQrCode()
    {
        return $this->_getData(self::DATA_QR_CODE);
    }

    public function setUrl(string $url)
    {
        return $this->setData(self::DATA_URL, $url);
    }

    public function setQrCode(string $qrCode)
    {
        return $this->setData(self::DATA_QR_CODE, $qrCode);
    }
}