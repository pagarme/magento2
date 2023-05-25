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

use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use PagarmeCoreApiLib\APIException;

/**
 * Class CardsManagementInterface
 * @package Pagarme\Pagarme\Api
 */
interface CardsManagementInterface
{
    /**
     * @param SavedCardInterface $card
     * @return void
     * @throws APIException
     */
    public function remove(SavedCardInterface $card);
}
