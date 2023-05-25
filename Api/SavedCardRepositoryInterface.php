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
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class SavedCardRepositoryInterface
 * @package Pagarme\Pagarme\Api
 */
interface SavedCardRepositoryInterface
{
    /**
     * Save saved_card
     * @param SavedCardInterface $entity
     * @return SavedCardInterface
     * @throws LocalizedException
     */
    public function save(
        SavedCardInterface $entity
    ): SavedCardInterface;

    /**
     * Retrieve saved_card
     * @param string $entityId
     * @return SavedCardInterface
     * @throws LocalizedException
     */
    public function get(string $entityId): SavedCardInterface;

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
     * @param SavedCardInterface $entity
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        SavedCardInterface $entity
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
