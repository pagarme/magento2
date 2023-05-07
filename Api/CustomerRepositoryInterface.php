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
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Api\Data\CustomerInterface;

/**
 * Class CustomerRepositoryInterface
 * @package Pagarme\Pagarme\Api
 */
interface CustomerRepositoryInterface
{
    /**
     * Save saved_card
     * @param CustomerInterface $entity
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function save(
        CustomerInterface $entity
    ): CustomerInterface;

    /**
     * Retrieve saved_card
     * @param string $entityId
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function get(string $entityId): CustomerInterface;

    /**
     * Retrieve saved_card matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface;

    /**
     * Delete saved_card
     * @param CustomerInterface $entity
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        CustomerInterface $entity
    ): bool;

    /**
     * Delete saved_card by ID
     * @param string $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $entityId): bool;
}
