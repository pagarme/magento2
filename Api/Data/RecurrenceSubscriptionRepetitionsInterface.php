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
 * Class RecurrenceSubscriptionRepetitionsInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface RecurrenceSubscriptionRepetitionsInterface
{
    /** @var string */
    const CYCLES = 'cycles';

    /** @var string */
    const SUBSCRIPTION_ID = 'subscription_id';

    /** @var string */
    const UPDATED_AT = 'updated_at';

    /** @var string */
    const ID = 'id';

    /** @var string */
    const INTERVAL_COUNT = 'interval_count';

    /** @var string */
    const INTERVAL = 'interval';

    /** @var string */
    const CREATED_AT = 'created_at';

    /** @var string */
    const RECURRENCE_PRICE = 'recurrence_price';

    /**
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * @param int $subscriptionId
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setSubscriptionId(int $subscriptionId): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return string
     */
    public function getInterval(): string;

    /**
     * @param string $interval
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setInterval(string $interval): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return string
     */
    public function getIntervalCount(): string;

    /**
     * @param string $intervalCount
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setIntervalCount(string $intervalCount): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return string|null
     */
    public function getRecurrencePrice(): ?string;

    /**
     * @param int|null $recurrencePrice
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setRecurrencePrice(?int $recurrencePrice): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return int|null
     */
    public function getCycles(): ?int;

    /**
     * @param int|null $cycles
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setCycles(?int $cycles): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setCreatedAt(string $createdAt): RecurrenceSubscriptionRepetitionsInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return RecurrenceSubscriptionRepetitionsInterface
     */
    public function setUpdatedAt(string $updatedAt): RecurrenceSubscriptionRepetitionsInterface;
}
