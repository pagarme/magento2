<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\Marketplace\SplitRuleTrait;

abstract class MarketplaceHandler
{
    use SplitRuleTrait;
    const ONLY_MARKETPLACE = 'marketplace';
    const MARKETPLACE_SELLERS = 'marketplace_sellers';
    const ONLY_SELLERS = 'sellers';

    protected $moduleConfig;

    public function __construct()
    {
        $this->moduleConfig = Magento2CoreSetup::getModuleConfiguration();
    }
}
