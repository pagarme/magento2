<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\ResourceModel\SavedCard;

/**
 * interface CollectionFactory
 * @package Pagarme\Pagarme\Model\ResourceModel\SavedCard
 */
interface CollectionFactoryInterface
{
    /**
     * @param string|null $ownerId
     * @return Collection
     */
    public function create(string $ownerId = null): Collection;
}
