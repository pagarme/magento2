<?php

namespace Pagarme\Pagarme\Service\Payment;

use Magento\Framework\Exception\LocalizedException;

class ClientPaymentAmountValidator
{
    const TOLERANCE_IN_CENTS = 1;

    /**
     * Converts a masked amount such as "1.234,56" or "50,00" into cents.
     *
     * @param mixed $maskedAmount
     * @return int
     * @throws LocalizedException
     */
    public function parseMaskedAmountToCents($maskedAmount)
    {
        $digits = str_replace(['.', ','], '', (string) $maskedAmount);

        if ($digits === '' || !ctype_digit($digits)) {
            throw new LocalizedException(__('The informed payment amount is invalid.'));
        }

        return (int) $digits;
    }

    /**
     * Converts a decimal amount in reais such as "12.34" into cents.
     *
     * @param mixed $decimalAmount
     * @return int
     * @throws LocalizedException
     */
    public function parseDecimalToCents($decimalAmount)
    {
        if ($decimalAmount === null || $decimalAmount === '') {
            return 0;
        }

        if (!is_numeric($decimalAmount)) {
            throw new LocalizedException(__('The informed interest amount is invalid.'));
        }

        return (int) round((float) $decimalAmount * 100);
    }

    /**
     * @param int $expectedInCents
     * @param mixed $clientDecimalAmount
     * @throws LocalizedException
     */
    public function assertInterestMatches($expectedInCents, $clientDecimalAmount)
    {
        $clientInCents = $this->parseDecimalToCents($clientDecimalAmount);

        if (abs($expectedInCents - $clientInCents) > self::TOLERANCE_IN_CENTS) {
            throw new LocalizedException(__('The informed interest amount is invalid.'));
        }
    }

    /**
     * @param int $grandTotalInCents
     * @param int[] $partsInCents
     * @throws LocalizedException
     */
    public function assertSplitMatchesTotal($grandTotalInCents, array $partsInCents)
    {
        foreach ($partsInCents as $part) {
            if ($part <= 0) {
                throw new LocalizedException(__('The informed payment amount is invalid.'));
            }
        }

        if (abs(array_sum($partsInCents) - $grandTotalInCents) > self::TOLERANCE_IN_CENTS) {
            throw new LocalizedException(__('The informed payment amount is invalid.'));
        }
    }
}
