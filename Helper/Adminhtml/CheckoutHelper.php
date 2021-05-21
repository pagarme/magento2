<?php

namespace Pagarme\Pagarme\Helper\Adminhtml;

use Magento\Framework\App\Helper\AbstractHelper;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Payment\Services\CardService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

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
        $publicKey = $config->getPublicKey();
        if (!$publicKey) {
            return null;
        }

        return  $publicKey->getValue();
    }

    public function getMonths()
    {
        // @todo get translate from dashboard
        return [
            "01" => "Janeiro",
            "02" => "Fevereiro",
            "03" => "Março",
            "04" => "Abril",
            "05" => "Maio",
            "06" => "Junho",
            "07" => "Julho",
            "08" => "Agosto",
            "09" => "Setembro",
            "10" => "Outubro",
            "11" => "Novembro",
            "12" => "Dezembro",
        ];
    }

    public function getYears()
    {
        return range(date("Y"), date("Y") + 10);
    }
    public function getInstallmentsUrl($baseUrl)
    {
        $defaultStoreViewCode = Magento2CoreSetup::getDefaultStoreViewCode();
        return $baseUrl . "rest/{$defaultStoreViewCode}/V1/pagarme/installments/brandbyamount";
    }

    public function formatGrandTotal($granTotal)
    {
        $number = number_format((float)$granTotal, 2, '.', '');
        return $number;
    }
}
