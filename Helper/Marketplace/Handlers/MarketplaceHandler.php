<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Handlers;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

abstract class MarketplaceHandler
{
    const ONLY_MARKETPLACE = 'marketplace';
    const MARKETPLACE_SELLERS = 'marketplace_sellers';
    const ONLY_SELLERS = 'sellers';

    protected $moduleConfig;

    public function __construct()
    {
        $this->moduleConfig = Magento2CoreSetup::getModuleConfiguration();
    }

    abstract protected function divideBetweenMarkeplaceAndSellers($amount, &$arrayData);
    abstract protected function divideBetweenSellers($amount, &$arrayData);
    abstract protected function onlyMarketplaceResponsible($amount, &$arrayData);
}
