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

namespace Pagarme\Pagarme\Gateway\Transaction\TwoCreditCard\Config;


interface ConfigInterface
{
    const PATH_ACTIVE                       = 'payment/pagarme_two_creditcard/active';
    const PATH_PAYMENT_ACTION               = 'payment/pagarme_two_creditcard/payment_action';
    const PATH_ANTIFRAUD_ACTIVE             = 'payment/pagarme_two_creditcard/antifraud_active';
    const PATH_ANTIFRAUD_MIN_AMOUNT         = 'payment/pagarme_two_creditcard/antifraud_min_amount';
    const PATH_SOFT_DESCRIPTION             = 'payment/pagarme_creditcard/soft_description';
    const PATH_CUSTOMER_STREET              = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER              = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT          = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT            = 'payment/pagarme_customer_address/district_attribute';
    const PATH_TITLE                        = 'payment/pagarme_two_creditcard/title';

    /**
     * @return bool
     */
    public function getActive();

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
    public function getSoftDescription();

    /**
     * @return string
     */
    public function getTitle();
}
