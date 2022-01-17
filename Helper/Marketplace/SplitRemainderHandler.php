<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Helper\Marketplace\MarketplaceHandler;

final class SplitRemainderHandler extends MarketplaceHandler
{
    private function getTotalSellerCommission(array $sellersData)
    {
        $totalCommission = 0;

        foreach ($sellersData as $commission) {
            $totalCommission += $commission['commission'];
        }

        return $totalCommission;
    }

    public function setRemainderToResponsible($remainder, $splitData)
    {
        $responsible = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getResponsibilityForReceivingSplitRemainder();

        switch ($responsible) {
            case self::ONLY_MARKETPLACE:
                $splitData['marketplace']['totalCommission'] += $remainder;
                return $splitData;
            case self::ONLY_SELLERS:
                return $this->divideBetweenSellers(
                    $remainder,
                    $splitData
                );
            case self::MARKETPLACE_SELLERS:
                return $this->divideBetweenMarkeplaceAndSellers(
                    $remainder,
                    $splitData
                );
        }
    }

    public function calculateRemainder(
        $splitData,
        $totalPaidProductWithoutSeller,
        $totalPaid
    ) {
        $totalSellerCommission
            = $this->getTotalSellerCommission($splitData['sellers']);

        $totalMarketplaceCommission
            = $splitData['marketplace']['totalCommission'];

        $remainder = $totalPaid - $totalPaidProductWithoutSeller
            - $totalSellerCommission - $totalMarketplaceCommission;

        return $remainder;
    }
}
