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
    public function getTitle();

}
