<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Service\Customer;

use Pagarme\Pagarme\Api\CardsManagementInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use Pagarme\Pagarme\Api\PagarmeServiceApiInterface;
use PagarmeCoreApiLib\APIException;

/**
 * Class CardsManagement
 * @package Pagarme\Pagarme\Service\Customer
 */
class CardsManagement implements CardsManagementInterface
{
    /**
     * @var PagarmeServiceApiInterface
     */
    private $api;

    /**
     * @param PagarmeServiceApiInterface $api
     */
    public function __construct(
        PagarmeServiceApiInterface $api
    ) {
        $this->api = $api;
    }

    /**
     * @param SavedCardInterface $card
     * @return void
     * @throws APIException
     */
    public function remove(SavedCardInterface $card)
    {
        $this->api->get()
            ->getCustomers()
            ->deleteCard(
                $card->getOwnerId(),
                $card->getPagarmeId()
            );
    }
}
