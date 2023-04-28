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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Class RecurrenceSubscriptionRepetitionsSearchResultsInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface RecurrenceSubscriptionRepetitionsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get recurrence_subscription_repetitions list.
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
