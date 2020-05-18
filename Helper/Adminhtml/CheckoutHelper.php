<?php

namespace MundiPagg\MundiPagg\Helper\Adminhtml;

use Magento\Framework\App\Helper\AbstractHelper;
use Mundipagg\Core\Payment\Services\CardService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class CheckoutHelper extends AbstractHelper
{

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
    }

    public function getBrandsAvailables($code)
    {
        $config = $this->getPaymentMethodConfig($code);
        if (!$config) {
            return [];
        }
        $cardService = new CardService();

        return $cardService->getBrandsAvailables($config);
    }

    public function getPaymentMethodConfig($code)
    {
        $method = explode('_', $code);
        $moduleConfigurations = Magento2CoreSetup::getModuleConfiguration();

        $methods = [
            'creditcard' => $moduleConfigurations,
            'voucher' => $moduleConfigurations->getVoucherConfig(),
            'debitcard' => $moduleConfigurations->getDebitConfig()
        ];

        if (!empty($methods[$method[1]])) {
            return $methods[$method[1]];
        }

        return null;
    }

    public function getPublicKey()
    {
        $config = Magento2CoreSetup::getModuleConfiguration();
        $publicKey= $config->getPublicKey();
        if (!$publicKey) {
            return null;
        }

        return  $publicKey->getValue();
    }
}