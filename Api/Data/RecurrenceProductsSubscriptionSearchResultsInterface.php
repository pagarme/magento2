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
 * Class RecurrenceProductsSubscriptionSearchResultsInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface RecurrenceProductsSubscriptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get recurrence_products_subscription list.
     * @return RecurrenceProductsSubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param RecurrenceProductsSubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
