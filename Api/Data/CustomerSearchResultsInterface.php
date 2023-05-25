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
 * Class CustomerSearchResultsInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface CustomerSearchResultsInterface
{
    /**
     * Get customer list.
     * @return CustomerInterface[]
     */
    public function getItems(): array;

    /**
     * Set customer list.
     * @param CustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items): CustomerSearchResultsInterface;
}
