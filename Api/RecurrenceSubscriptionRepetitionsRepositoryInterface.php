<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsSearchResultsInterface;

/**
 * Class RecurrenceProductsSubscriptionRepositoryInterface
 * @package Pagarme\Pagarme\Api
 */
interface RecurrenceSubscriptionRepetitionsRepositoryInterface
{
    /**
     * Save recurrence_subscription_repetitions
     * @param RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return RecurrenceSubscriptionRepetitionsInterface
     * @throws LocalizedException
     */
    public function save(
        RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    );

    /**
     * Retrieve recurrence_subscription_repetitions
     * @param string $recurrenceSubscriptionRepetitionsId
     * @return RecurrenceSubscriptionRepetitionsInterface
     * @throws LocalizedException
     */
    public function get($recurrenceSubscriptionRepetitionsId);

    /**
     * Retrieve recurrence_subscription_repetitions matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return RecurrenceSubscriptionRepetitionsSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete recurrence_subscription_repetitions
     * @param RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    );

    /**
     * Delete recurrence_subscription_repetitions by ID
     * @param string $recurrenceSubscriptionRepetitionsId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($recurrenceSubscriptionRepetitionsId);
}
