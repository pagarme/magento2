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
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

/**
 * Class RecurrenceProductsSubscriptionRepositoryInterface
 * @package Pagarme\Pagarme\Api
 */
interface RecurrenceProductsSubscriptionRepositoryInterface
{
    /**
     * Save recurrence_products_subscription
     * @param RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
     * @return RecurrenceProductsSubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
    ): RecurrenceProductsSubscriptionInterface;

    /**
     * Retrieve recurrence_products_subscription
     * @param string $recurrenceProductsSubscriptionId
     * @return RecurrenceProductsSubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($recurrenceProductsSubscriptionId): RecurrenceProductsSubscriptionInterface;

    /**
     * Retrieve recurrence_products_subscription matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete recurrence_products_subscription
     * @param RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
    );

    /**
     * Delete recurrence_products_subscription by ID
     * @param string $recurrenceProductsSubscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($recurrenceProductsSubscriptionId);
}
