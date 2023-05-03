<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\ResourceModel\RecurrenceSubscriptionRepetitions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;

/**
 * Class Collection
 * @package Pagarme\Pagarme\Model\ResourceModel\RecurrenceSubscriptionRepetitions
 */
class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = RecurrenceSubscriptionRepetitionsInterface::ID;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Pagarme\Pagarme\Model\RecurrenceSubscriptionRepetitions::class,
            \Pagarme\Pagarme\Model\ResourceModel\RecurrenceSubscriptionRepetitions::class
        );
    }
}
