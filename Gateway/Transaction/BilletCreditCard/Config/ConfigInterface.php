<?php
/**
 * Class ConfigInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\BilletCreditCard\Config;


interface ConfigInterface
{
    const PATH_ACTIVE                       = 'payment/pagarme_billet_creditcard/active';
    const PATH_IS_ONE_DOLLAR_AUTH_ENABLED   = 'payment/pagarme_billet_creditcard/is_one_dollar_auth_enabled';
    const PATH_PAYMENT_ACTION               = 'payment/pagarme_billet_creditcard/payment_action';
    const PATH_ANTIFRAUD_ACTIVE             = 'payment/pagarme_billet_creditcard/antifraud_active';
    const PATH_ANTIFRAUD_MIN_AMOUNT         = 'payment/pagarme_billet_creditcard/antifraud_min_amount';
    const PATH_CUSTOMER_STREET              = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER              = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT          = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT            = 'payment/pagarme_customer_address/district_attribute';
    const PATH_TITLE                        = 'payment/pagarme_billet_creditcard/title';

    /**
     * @return bool
     */
    public function getActive();

    /**
     * @return bool
     */
    public function getIsOneDollarAuthEnabled();

    /**
     * @return string
     */
    public function getPaymentAction();

    /**
     * @return bool
     */
    public function getAntifraudActive();

    /**
     * @return string
     */
    public function getAntifraudMinAmount();

    /**
     * @return string
     */
    public function getCustomerStreetAttribute();

    /**
     * @return string
     */
    public function getCustomerAddressNumber();

    /**
     * @return string
     */
    public function getCustomerAddressComplement();

    /**
     * @return string
     */
    public function getCustomerAddressDistrict();

    /**
     * @return string
     */
    public function getTitle();
}
