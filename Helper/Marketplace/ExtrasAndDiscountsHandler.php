<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Helper\Marketplace\MarketplaceHandler;

final class ExtrasAndDiscountsHandler extends MarketplaceHandler
{
    public function calculateExtraOrDiscount($totalPaid, $productTotal)
    {
        return $totalPaid - $productTotal;
    }

    public function setExtraOrDiscountToResponsible($extraOrDiscount, $splitData)
    {
        $responsible = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getResponsibilityForReceivingExtrasAndDiscounts();

        switch ($responsible) {
            case self::ONLY_MARKETPLACE:
                $splitData['marketplace']['totalCommission'] += $extraOrDiscount;
                return $splitData;
            case self::ONLY_SELLERS:
                return $this->divideBetweenSellers(
                    $extraOrDiscount,
                    $splitData
                );
            case self::MARKETPLACE_SELLERS:
                return $this->divideBetweenMarkeplaceAndSellers(
                    $extraOrDiscount,
                    $splitData
                );
        }

        return $splitData;
    }
}
