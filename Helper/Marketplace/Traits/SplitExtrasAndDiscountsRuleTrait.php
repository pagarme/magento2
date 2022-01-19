<?php

namespace Pagarme\Pagarme\Helper\Marketplace\Traits;

use Pagarme\Pagarme\Helper\Marketplace\Handlers\SplitRemainderHandler;

trait SplitExtrasAndDiscoutsRuleTrait
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
        return $commission / $this->totalPaid;
    }

    private function calculateAmountForMarketplace($splitData, $amount)
    {
        $marketplaceCommission = $splitData['marketplace']['totalCommission'];
        $marketplaceExtrasAndDiscountsPercentage =
            $this->getPercentageOfTotalPaidPerEntity($marketplaceCommission);

        return intval(
            floor(
                $marketplaceExtrasAndDiscountsPercentage * $amount
            )
        );
    }

    private function calculateAmountForSeller($seller, $amount)
    {
        $sellerCommission = $seller['commission'];
        $sellerExtrasAndDiscountsPercentage =
            $this->getPercentageOfTotalPaidPerEntity($sellerCommission);

        return intval(
            floor($sellerExtrasAndDiscountsPercentage * $amount)
        );
    }

    private function getQuantityOfSellers($splitData)
    {
        $quantityOfSellers = 0;

        foreach ($splitData['sellers'] as $key => $seller) {
            $quantityOfSellers++;
        }

        return $quantityOfSellers;
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

    protected function divideBetweenMarkeplaceAndSellers(
        $amount,
        &$splitData
    ) {

        $amountForMarketplace = $this->calculateAmountForMarketplace(
            $splitData,
            $amount
        );

        $amountTotal = $amountForMarketplace;
        $splitData['marketplace']['totalCommission'] += $amountForMarketplace;

        foreach ($splitData['sellers'] as $key => &$seller) {
            $amountForSeller = $this->calculateAmountForSeller($seller, $amount);

            $seller['commission'] += $amountForSeller;
            $amountTotal += $amountForSeller;
        }

        if ($amountTotal < $amount) {
            $remainder = $amount - $amountTotal;
            $splitData = $this->splitRemainderHandler
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $this->verifyZeroCommission($splitData);
    }

    protected function divideBetweenSellers(
        $amount,
        &$splitData
    ) {
        $amountTotal = 0;

        foreach ($splitData['sellers'] as $key => &$seller) {
            $amountForSeller = $this->calculateAmountForSeller($seller, $amount);

            $seller['commission'] += $amountForSeller;
            $amountTotal += $amountForSeller;
        }

        if ($amountTotal < $amount) {
            $remainder = $amount - $amountTotal;
            $splitData = $this->splitRemainderHandler
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
            $splitData = $this->splitRemainderHandler
                ->setRemainderToResponsible($remainder, $splitData);
        }

        return $splitData;
    }
}
