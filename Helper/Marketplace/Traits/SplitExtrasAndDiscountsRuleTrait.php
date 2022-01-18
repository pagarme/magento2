<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Traits;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\SplitRemainderHandler;

trait SplitExtrasAndDiscoutsRuleTrait
{
    private $splitRemainderHandler = null;

    private function getSplitRemainder()
    {
        if (!$this->splitRemainderHandler) {
            $this->splitRemainderHandler = new SplitRemainderHandler();
        }

        return $this->splitRemainderHandler;
    }

    private function getQuantityOfSellers($splitData)
    {
        $quantityOfSellers = 0;

        foreach ($splitData['sellers'] as $key => $seller) {
            $quantityOfSellers++;
        }

        return $quantityOfSellers;
    }

    protected function divideBetweenMarkeplaceAndSellers(
        $amount,
        $splitData
    ) {
        return $splitData;
    }

    protected function divideBetweenSellers(
        $amount,
        $splitData
    ) {
        return $splitData;
    }

    protected function divideBetweenNonZeroCommission($amount, $splitData)
    {
        return $splitData;
    }
}
