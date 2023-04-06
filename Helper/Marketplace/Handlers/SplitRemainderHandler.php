<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Handlers;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\MarketplaceHandler;
use Pagarme\Pagarme\Helper\Marketplace\Traits\SplitRemainderRuleTrait;

final class SplitRemainderHandler extends MarketplaceHandler
{
    use SplitRemainderRuleTrait;
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
                return $this->onlyMarketplaceResponsible(
                    $remainder,
                    $splitData
                );
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
            - intval($totalSellerCommission) - intval($totalMarketplaceCommission);

        if ($remainder < 0) {
            throw new \Exception("found negative remainder: $remainder");
        }

        return $remainder;
    }
}
