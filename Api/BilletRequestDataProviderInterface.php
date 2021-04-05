<?php
/**
 * Class CreateCreditCardDataProviderInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Api;


interface BilletRequestDataProviderInterface extends BaseRequestDataProviderInterface
{
    /**
     * @return string
     */
    public function getBankType();

    /**
     * @return string
     */
    public function getInstructions();

    /**
     * @return string
     */
    public function getDaysToAddInBoletoExpirationDate();

    /**
     * @return string
     */
    public function getCustomerAddressStreet($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressNumber($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressComplement($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressDistrict($shipping);
}
