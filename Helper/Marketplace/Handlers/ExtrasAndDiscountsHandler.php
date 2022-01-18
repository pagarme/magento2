<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Handlers;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\MarketplaceHandler;
use Pagarme\Pagarme\Helper\Marketplace\Traits\SplitExtrasAndDiscoutsRuleTrait;

final class ExtrasAndDiscountsHandler extends MarketplaceHandler
{
    use SplitExtrasAndDiscoutsRuleTrait;

    private function handleMarketplaceNegativeCommission(&$splitData, $negativeAmount)
    {
        $splitData['marketplace']['totalCommission'] = 0;

        return $this->divideBetweenNonZeroCommission(
            -$negativeAmount,
            $splitData
        );
    }

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
                $marketPlaceCommission = $splitData['marketplace']['totalCommission'];
                $marketPlaceAndExtraOrDiscount = $marketPlaceCommission + $extraOrDiscount;

                if ($marketPlaceAndExtraOrDiscount < 0) {
                    return $this->handleMarketplaceNegativeCommission(
                        $splitData,
                        $marketPlaceAndExtraOrDiscount
                    );
                }

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
