<?php
/**
 * Class CreateBilletCreditCardDataProviderInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Api;


interface BilletCreditCardRequestDataProviderInterface extends BaseRequestDataProviderInterface
{

    /**
     * @return int
     */
    public function getBankType();

    /**
     * @return int
     */
    public function getInstallmentCount();

    /**
     * @return string
     */
    public function getCcTokenCreditCard();

    /**
     * @return int
     */
    public function getSaveCard();

    /**
     * @return string
     */
    public function getBilletCreditCardOperation();

    /**
     * @return string
     */
    public function getBilletCreditCardBrand();

    /**
     * @return string
     */
    public function getBilletCreditCardNumber();

    /**
     * @return string
     */
    public function getExpMonth();

    /**
     * @return string
     */
    public function getExpYear();

    /**
     * @return string
     */
    public function getHolderName();

    /**
     * @return string
     */
    public function getSecurityCode();

    /**
     * @return string
     */
    public function getIsOneDollarAuthEnabled();

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
