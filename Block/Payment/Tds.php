<?php

namespace Pagarme\Pagarme\Block\Payment;

use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config as CreditCardConfig;
use Pagarme\Pagarme\Gateway\Transaction\DebitCard\Config\Config as DebitCardConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config;
// use 

class Tds extends \Magento\Framework\View\Element\Template
{

    private DebitCardConfig $debitCardConfig;
    private CreditCardConfig $creditCardConfig;
    protected Config $config;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\View\Page\Config  $pageConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CreditCardConfig $creditCardConfig,
        DebitCardConfig $debitCardConfig,
        array $data = []
    ) {
        
        $this->config = $config;
        $this->creditCardConfig = $creditCardConfig;
        $this->debitCardConfig = $debitCardConfig;
        // $this->addJs();
        parent::__construct($context, $data);
    }

    
    public function addJs()
    {
        $url = 'https://auth-3ds-sdx.pagar.me/bundle.js';
        if ($this->canInitTds()) {
            $this->config->addRemotePageAsset($url, 'js', [], 'pagarme-tds');
        }
    }

    public function canInitTds()
    {
        return $this->creditCardConfig->getTdsActive() || $this->debitCardConfig->getTdsActive();
    }
}
