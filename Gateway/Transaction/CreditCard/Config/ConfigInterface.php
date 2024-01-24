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

namespace Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config;


interface ConfigInterface
{
    const PATH_ACTIVE                       = 'payment/pagarme_creditcard/active';
    const PATH_ENABLED_SAVED_CARDS          = 'payment/pagarme_creditcard/enabled_saved_cards';
    const PATH_TDS_ACTIVE                   = 'payment/pagarme_creditcard/tds_active';
    const PATH_ORDER_WITH_TDS_REFUSED       = 'payment/pagarme_creditcard/order_with_tds_refused';
    const PATH_TDS_MIN_AMOUNT               = 'payment/pagarme_creditcard/tds_min_amount';
    const PATH_PAYMENT_ACTION               = 'payment/pagarme_creditcard/payment_action';
    const PATH_ANTIFRAUD_ACTIVE             = 'payment/pagarme_creditcard/antifraud_active';
    const PATH_ANTIFRAUD_MIN_AMOUNT         = 'payment/pagarme_creditcard/antifraud_min_amount';
    const PATH_SOFT_DESCRIPTION             = 'payment/pagarme_creditcard/soft_description';
    const PATH_TITLE                        = 'payment/pagarme_creditcard/title';

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
