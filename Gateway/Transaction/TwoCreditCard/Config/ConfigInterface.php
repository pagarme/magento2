<?php
/**
 * Class ConfigInterface
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\TwoCreditCard\Config;


interface ConfigInterface
{
    const PATH_ACTIVE                       = 'payment/mundipagg_two_creditcard/active';
    const PATH_PAYMENT_ACTION               = 'payment/mundipagg_two_creditcard/payment_action';
    const PATH_ANTIFRAUD_ACTIVE             = 'payment/mundipagg_two_creditcard/antifraud_active';
    const PATH_ANTIFRAUD_MIN_AMOUNT         = 'payment/mundipagg_two_creditcard/antifraud_min_amount';
    const PATH_SOFT_DESCRIPTION             = 'payment/mundipagg_creditcard/soft_description';

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
}
