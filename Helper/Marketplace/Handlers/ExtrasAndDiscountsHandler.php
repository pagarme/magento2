<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Handlers;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\MarketplaceHandler;
use Pagarme\Pagarme\Helper\Marketplace\Traits\SplitExtrasAndDiscountsRuleTrait;

final class ExtrasAndDiscountsHandler extends MarketplaceHandler
{
    use SplitExtrasAndDiscountsRuleTrait;

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
        $this->totalPaid = $totalPaid;
        $this->productTotal = $productTotal;

        return $totalPaid - $productTotal;
    }

    public function setExtraOrDiscountToResponsible($extraOrDiscount, $splitData)
    {
        $responsible = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getResponsibilityForReceivingExtrasAndDiscounts();

        switch ($responsible) {
            case self::ONLY_MARKETPLACE:
                return $this->onlyMarketplaceResponsible(
                    $extraOrDiscount,
                    $splitData
                );
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
