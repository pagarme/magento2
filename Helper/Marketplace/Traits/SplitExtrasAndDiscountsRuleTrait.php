<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Traits;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\SplitRemainderHandler;

trait SplitExtrasAndDiscountsRuleTrait
{
    private $splitRemainderHandler = null;
    protected $totalPaid;
    protected $productTotal;

    private function getSplitRemainder()
    {
        if (!$this->splitRemainderHandler) {
            $this->splitRemainderHandler = new SplitRemainderHandler();
        }

        return $this->splitRemainderHandler;
    }

    private function getPercentageOfTotalPaidPerEntity($commission)
    {
        return $commission / $this->productTotal;
    }

    private function calculateAmountForMarketplace($splitData, $amount)
    {
        $marketplaceCommission = $splitData['marketplace']['totalCommission'];
        $marketplaceExtrasAndDiscountsPercentage =
            $this->getPercentageOfTotalPaidPerEntity($marketplaceCommission);

        return intval(
            $marketplaceExtrasAndDiscountsPercentage * $amount
        );
    }

    private function calculateAmountForSeller($seller, $amount)
    {
        $sellerCommission = $seller['commission'];
        $sellerExtrasAndDiscountsPercentage =
            $this->getPercentageOfTotalPaidPerEntity($sellerCommission);

        return intval($sellerExtrasAndDiscountsPercentage * $amount);
    }

    private function calculateAmountOnlyForSeller($seller, $amount, $marketplaceTotal){
        $sellerCommission = $seller['commission'];
        $sellerExtrasAndDiscountsPercentage =
            $sellerCommission / ($this->productTotal - $marketplaceTotal);

        return intval($sellerExtrasAndDiscountsPercentage * $amount);
    }

    private function getQuantityOfSellers($splitData)
    {
        $quantityOfSellers = 0;

        foreach ($splitData['sellers'] as $key => $seller) {
            $quantityOfSellers++;
        }

        return $quantityOfSellers;
    }

    private function getRemainder(&$splitData, $extrasAndDiscountsTotal)
    {
        $splitData['marketplace']['totalCommission'] = intval(
            $splitData['marketplace']['totalCommission']
        );

        $integerTotal = $splitData['marketplace']['totalCommission'];

        foreach ($splitData['sellers'] as $key => &$seller) {
            $seller['commission'] = intval($seller['commission']);
            $integerTotal += $seller['commission'];
        }

        return ($this->productTotal + $extrasAndDiscountsTotal) - $integerTotal;
    }

    private function verifyZeroCommission(&$splitData)
    {
        $negativeAmount = 0;

        if ($splitData['marketplace']['totalCommission'] < 0) {
            $negativeAmount += $splitData['marketplace']['totalCommission'];
            $splitData['marketplace']['totalCommission'] = 0;
        }

        foreach ($splitData['sellers'] as $key => &$seller) {
            if ($seller['commission'] < 0) {
                $negativeAmount += $seller['commission'];
                $seller['commission'] = 0;
            }
        }

        if ($negativeAmount === 0) {
            return $splitData;
        }

        return $this->divideBetweenNonZeroCommission(
            $negativeAmount,
            $splitData
        );
    }

    protected function onlyMarketplaceResponsible($amount, &$splitData)
    {
        $marketPlaceCommission = $splitData['marketplace']['totalCommission'];
        $marketPlaceAndExtraOrDiscount = $marketPlaceCommission + $amount;

        if ($marketPlaceAndExtraOrDiscount < 0) {
            return $this->handleMarketplaceNegativeCommission(
                $splitData,
                $marketPlaceAndExtraOrDiscount
            );
        }

        $splitData['marketplace']['totalCommission'] += $amount;

        $remainder = $this->getRemainder($splitData, $amount);

        if ($remainder) {
            $splitData = $this->getSplitRemainder()
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $splitData;
    }

    protected function divideBetweenMarkeplaceAndSellers(
        $amount,
        &$splitData
    ) {

        $amountForMarketplace = $this->calculateAmountForMarketplace(
            $splitData,
            $amount
        );

        $splitData['marketplace']['totalCommission'] += $amountForMarketplace;

        foreach ($splitData['sellers'] as $key => &$seller) {
            $amountForSeller = $this->calculateAmountForSeller($seller, $amount);

            $seller['commission'] += $amountForSeller;
        }

        $remainder = $this->getRemainder($splitData, $amount);

        if ($remainder) {
            $splitData = $this->getSplitRemainder()
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $this->verifyZeroCommission($splitData);
    }

    protected function divideBetweenSellers(
        $amount,
        &$splitData
    ) {
        foreach ($splitData['sellers'] as $key => &$seller) {
            $amountForSeller = $this->calculateAmountOnlyForSeller(
                $seller, $amount, $splitData['marketplace']['totalCommission']);
                
            $seller['commission'] += $amountForSeller;
        }

        $remainder = $this->getRemainder($splitData, $amount);

        if ($remainder) {
            $splitData = $this->getSplitRemainder()
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $this->verifyZeroCommission($splitData);;
    }

    protected function divideBetweenNonZeroCommission($negativeAmount, &$splitData)
    {
        $this->total += -$negativeAmount;
        $amountTotal = 0;

        if (!empty($splitData['marketplace']['totalCommission'])) {
            $amountForMarketplace = $this->calculateAmountForMarketplace(
                $splitData,
                $negativeAmount
            );
            $amountTotal += $amountForMarketplace;
            $splitData['marketplace']['totalCommission'] += $amountForMarketplace;
        }

        foreach ($splitData['sellers'] as $key => &$seller) {
            if (!empty($seller['commission'])) {
                $amountForSeller = $this->calculateAmountForSeller(
                    $seller,
                    $negativeAmount
                );

                $amountTotal += $amountForSeller;
                $seller['commission'] += $amountForSeller;
            }
        }

        if ($amountTotal < $negativeAmount) {
            $remainder = $negativeAmount - $amountTotal;
            $splitData = $this->getSplitRemainder()
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $splitData;
    }
}
