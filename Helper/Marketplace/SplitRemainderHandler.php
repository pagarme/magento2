<?php

namespace Pagarme\Pagarme\Helper\Marketplace;

use Pagarme\Pagarme\Helper\Marketplace\MarketplaceHandler;

class SplitRemainderHandler extends MarketplaceHandler
{
    private function divideRemainderBetweenMarkeplaceAndSellers(
        $remainder,
        $splitData
    ) {
        $splitData['marketplace']['totalCommission'] += 1;
        $remainder -= 1;
        if ($remainder == 0) {
            return $splitData;
        }

        return $this->divideRemainderBetweenSellers($remainder, $splitData);
    }

    private function divideRemainderBetweenSellers(
        $remainder,
        $splitData
    ) {
        foreach ($splitData['sellers'] as $key => $seller) {
            $seller['commission'] += 1;
            $remainder -= 1;

            if ($remainder == 0) {
                $splitData['sellers'][$key] = $seller;
                return $splitData;
            }

            $splitData['sellers'][$key] = $seller;
        }

        return $this->divideRemainderBetweenMarkeplaceAndSellers(
            $remainder,
            $splitData
        );
    }

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
                return $this->divideRemainderBetweenSellers(
                    $remainder,
                    $splitData
                );
            case self::MARKETPLACE_SELLERS:
                return $this->divideRemainderBetweenMarkeplaceAndSellers(
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
