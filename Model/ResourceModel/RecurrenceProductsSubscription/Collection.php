<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

/**
 * Class Collection
 * @package Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription
 */
class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = RecurrenceProductsSubscriptionInterface::ID;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Pagarme\Pagarme\Model\RecurrenceProductsSubscription::class,
            \Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription::class
        );
    }
}
