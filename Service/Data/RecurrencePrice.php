<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Service\Data;

use Magento\Framework\DataObject;
use Pagarme\Pagarme\Api\Data\RecurrencePriceInterface;

/**
 * Class RecurrencePrice
 * @package Pagarme\Pagarme\Service\Data
 */
class RecurrencePrice extends DataObject implements RecurrencePriceInterface
{
    /**
     * @param float $price
     * @return RecurrencePriceInterface
     */
    public function setPrice(float $price): RecurrencePriceInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @param string $interval
     * @return RecurrencePriceInterface
     */
    public function setInterval(string $interval): RecurrencePriceInterface
    {
        return $this->setData(self::INTERVAL, $interval);
    }

    /**
     * @return string|null
     */
    public function getInterval(): ?string
    {
        return $this->getData(self::INTERVAL);
    }

    /**
     * @param int $intervalCount
     * @return RecurrencePriceInterface
     */
    public function setIntervalCount(int $intervalCount): RecurrencePriceInterface
    {
        return $this->setData(self::INTERVAL_COUNT, $intervalCount);
    }

    /**
     * @return int|null
     */
    public function getIntervalCount(): ?int
    {
        return $this->getData(self::INTERVAL_COUNT);
    }
}
