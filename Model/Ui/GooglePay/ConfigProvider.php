<?php

namespace Pagarme\Pagarme\Model\Ui\GooglePay;

use Magento\Checkout\Model\ConfigProviderInterface;
use Pagarme\Pagarme\Gateway\Transaction\GooglePay\Config\ConfigInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'pagarme_googlepay';

    /**
     * @var ConfigInterface
     */
    private $googlePayConfig;

    public function __construct(
        ConfigInterface $googlePayConfig
    )
    {
        $this->googlePayConfig = $googlePayConfig;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'active' => true,
                    'title' => $this->googlePayConfig->getTitle(),
                    'merchantId' => $this->googlePayConfig->getMerchantId(),
                    'merchantName' => $this->googlePayConfig->getMerchantName(),
                    'cardBrands' => $this->googlePayConfig->getCardBrands()
                ]
            ]
        ];
    }
}
