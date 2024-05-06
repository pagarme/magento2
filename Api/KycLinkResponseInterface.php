<?php

namespace Pagarme\Pagarme\Api;

interface KycLinkResponseInterface
{
    const DATA_URL = 'url';
    const DATA_QR_CODE = 'qrCode';

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getQrCode();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url);

    /**
     * @param string $qrCode
     * @return $this
     */
    public function setQrCode(string $qrCode);
}