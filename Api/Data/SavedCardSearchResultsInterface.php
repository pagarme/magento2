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
 * Class SavedCardSearchResultsInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface SavedCardSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get saved_card list.
     * @return SavedCardInterface[]
     */
    public function getItems(): array;

    /**
     * Set saved_card list.
     * @param SavedCardInterface[] $items
     * @return $this
     */
    public function setItems(array $items): SavedCardSearchResultsInterface;
}
