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

namespace Pagarme\Pagarme\Gateway\Transaction\DebitCard\Config;


interface ConfigInterface
{
    const PATH_ACTIVE                       = 'payment/pagarme_debit/active';
    const PATH_ENABLED_SAVED_CARDS          = 'payment/pagarme_debit/enabled_saved_cards';
    const PATH_TDS_ACTIVE                   = 'payment/pagarme_debit/tds_active';
    const PATH_ORDER_WITH_TDS_REFUSED       = 'payment/pagarme_debit/order_with_tds_refused';
    const PATH_PAYMENT_ACTION               = 'payment/pagarme_debit/payment_action';
    const PATH_CUSTOMER_STREET              = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER              = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT          = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT            = 'payment/pagarme_customer_address/district_attribute';
    const PATH_TITLE                        = 'payment/pagarme_debit/title';

    /**
     * @return bool
     */
    public function getActive();

    /**
     * @return bool
     */
    public function getEnabledSavedCards();

    /**
     * @return string
     */
    public function getPaymentAction();

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
