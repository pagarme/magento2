<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class SavedCard
 * @package Pagarme\Pagarme\Model\ResourceModel
 */
class SavedCard extends AbstractDb
{
    /** @var string */
    const TABLE = 'pagarme_module_core_saved_card';

    /** @var string */
    const ENTITY_ID = SavedCardInterface::ENTITY_ID;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, self::ENTITY_ID);
    }
}
