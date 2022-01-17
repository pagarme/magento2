<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

abstract class MarketplaceHandler
{
    const ONLY_MARKETPLACE = 'marketplace';
    const MARKETPLACE_SELLERS = 'marketplace_sellers';
    const ONLY_SELLERS = 'sellers';

    private $moduleConfig;

    public function __construct()
    {
        $this->moduleConfig = Magento2CoreSetup::getModuleConfiguration();
    }
}
