<?php

namespace Pagarme\Pagarme\Block\Payment;

use Magento\Framework\View\Element\Template;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config as PagarmeConfig;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config as CreditCardConfig;
use Pagarme\Pagarme\Gateway\Transaction\DebitCard\Config\Config as DebitCardConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config;

class Tds extends Template
{

    /**
     * @var DebitCardConfig
     */
    private $debitCardConfig;

    /**
     * @var CreditCardConfig
     */
    private $creditCardConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PagarmeConfig
     */
    private $pagarmeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param CreditCardConfig $creditCardConfig
     * @param DebitCardConfig $debitCardConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CreditCardConfig $creditCardConfig,
        DebitCardConfig $debitCardConfig,
        PagarmeConfig $pagarmeConfig,
        array $data = []
    ) {

        $this->config = $config;
        $this->creditCardConfig = $creditCardConfig;
        $this->debitCardConfig = $debitCardConfig;
        $this->pagarmeConfig = $pagarmeConfig;
        parent::__construct($context, $data);
    }


    public function getSdkUrl()
    {
        $url = 'https://auth-3ds.pagar.me/bundle.js';
        if ($this->pagarmeConfig->isSandboxMode()) {
            $url = 'https://auth-3ds-sdx.pagar.me/bundle.js';
        }
        return $url;
    }

    public function canInitTds()
    {
        return $this->creditCardConfig->getTdsActive() || $this->debitCardConfig->getTdsActive();
    }
}
