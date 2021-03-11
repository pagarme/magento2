<?php
/**
 * Class CartItemRequestProvider
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Api;


interface CartItemRequestDataProviderInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getItemReference();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @return float
     */
    public function getUnitCostInCents();

    /**
     * @return float
     */
    public function getTotalCostInCents();
}
