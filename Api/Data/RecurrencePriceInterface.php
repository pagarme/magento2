<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Api\Data;

/**
 * Class RecurrencePriceInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface RecurrencePriceInterface
{
    /** @var string */
    const PRICE = 'price';

    /** @var string */
    const INTERVAL = 'interval';

    /** @var string */
    const INTERVAL_COUNT = 'interval_count';

    /**
     * @param float $price
     * @return RecurrencePriceInterface
     */
    public function setPrice(float $price): RecurrencePriceInterface;

    /**
     * @return float|null
     */
    public function getPrice(): ?float;

    /**
     * @param string $interval
     * @return RecurrencePriceInterface
     */
    public function setInterval(string $interval): RecurrencePriceInterface;

    /**
     * @return string|null
     */
    public function getInterval(): ?string;

    /**
     * @param int $intervalCount
     * @return RecurrencePriceInterface
     */
    public function setIntervalCount(int $intervalCount): RecurrencePriceInterface;

    /**
     * @return int|null
     */
    public function getIntervalCount(): ?int;
}
