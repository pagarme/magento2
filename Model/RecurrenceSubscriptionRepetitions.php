<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Model\AbstractModel;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;

/**
 * Class RecurrenceProductsSubscription
 * @package Pagarme\Pagarme\Model
 */
class RecurrenceSubscriptionRepetitions extends AbstractModel implements RecurrenceSubscriptionRepetitionsInterface
{
    /**
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(ResourceModel\RecurrenceSubscriptionRepetitions::class);
    }

    /**
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @param int $subscriptionId
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setSubscriptionId(int $subscriptionId): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->getData(self::INTERVAL);
    }

    /**
     * @param string $interval
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setInterval(string $interval): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::INTERVAL, $interval);
    }

    /**
     * @return string
     */
    public function getIntervalCount(): string
    {
        return $this->getData(self::INTERVAL_COUNT);
    }

    /**
     * @param string $intervalCount
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setIntervalCount(string $intervalCount): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::INTERVAL_COUNT, $intervalCount);
    }

    /**
     * @return string|null
     */
    public function getRecurrencePrice(): ?string
    {
        return $this->getData(self::RECURRENCE_PRICE);
    }

    /**
     * @param int|null $recurrencePrice
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setRecurrencePrice(?int $recurrencePrice): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::RECURRENCE_PRICE, $recurrencePrice);
    }

    /**
     * @return int|null
     */
    public function getCycles(): ?int
    {
        return $this->getData(self::CYCLES);
    }

    /**
     * @param int|null $cycles
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setCycles(?int $cycles): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::CYCLES, $cycles);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setCreatedAt(string $createdAt): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setUpdatedAt(string $updatedAt): RecurrenceSubscriptionRepetitionsInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
