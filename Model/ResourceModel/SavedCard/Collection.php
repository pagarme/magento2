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

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class Collection
 * @package Pagarme\Pagarme\Model\ResourceModel\SavedCard
 */
class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = SavedCardInterface::ENTITY_ID;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Pagarme\Pagarme\Model\SavedCard::class,
            \Pagarme\Pagarme\Model\ResourceModel\SavedCard::class
        );
    }
}
