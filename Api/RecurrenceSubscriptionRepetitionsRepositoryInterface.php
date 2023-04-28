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

/**
 * Class RecurrenceProductsSubscriptionRepositoryInterface
 * @package Pagarme\Pagarme\Api
 */
interface RecurrenceSubscriptionRepetitionsRepositoryInterface
{
    /**
     * Save recurrence_subscription_repetitions
     * @param \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    );

    /**
     * Retrieve recurrence_subscription_repetitions
     * @param string $recurrenceSubscriptionRepetitionsId
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($recurrenceSubscriptionRepetitionsId);

    /**
     * Retrieve recurrence_subscription_repetitions matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete recurrence_subscription_repetitions
     * @param \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    );

    /**
     * Delete recurrence_subscription_repetitions by ID
     * @param string $recurrenceSubscriptionRepetitionsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($recurrenceSubscriptionRepetitionsId);
}
