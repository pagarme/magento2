<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Helper\Marketplace\MarketplaceHandler;

class ExtrasAndDiscountsHandler extends MarketplaceHandler
{
    public function calculateExtraOrDiscount($totalPaid, $productTotal)
    {
        return $totalPaid - $productTotal;
    }

    public function setExtraOrDiscountToResponsible($amount, $splitData)
    {
        return $splitData;
    }
}
