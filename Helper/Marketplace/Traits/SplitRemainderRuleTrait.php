<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Traits;

trait SplitRemainderRuleTrait
{
    protected function divideBetweenMarkeplaceAndSellers(
        $amount,
        &$splitData
    ) {
        $splitData['marketplace']['totalCommission'] += 1;
        $amount -= 1;
        if ($amount == 0) {
            return $splitData;
        }

        return $this->divideBetweenSellers($amount, $splitData);
    }

    protected function onlyMarketplaceResponsible($amount, &$splitData)
    {
        $splitData['marketplace']['totalCommission'] += $amount;
        return $splitData;
    }

    protected function divideBetweenSellers(
        $amount,
        &$splitData
    ) {
        foreach ($splitData['sellers'] as $key => $seller) {
            $seller['commission'] += 1;
            $amount -= 1;

            if ($amount == 0) {
                $splitData['sellers'][$key] = $seller;
                return $splitData;
            }

            $splitData['sellers'][$key] = $seller;
        }

        return $this->divideBetweenMarkeplaceAndSellers(
            $amount,
            $splitData
        );
    }
}
