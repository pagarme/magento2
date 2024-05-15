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

namespace Pagarme\Pagarme\Gateway\Transaction\GooglePay\Config;


interface ConfigInterface
{
    const PATH_TITLE        = 'payment/pagarme_googlepay/title';
    const MERCHANT_ID       = 'payment/pagarme_googlepay/merchant_id';
    const MERCHANT_NAME     = 'payment/pagarme_googlepay/merchant_name';
    /**
     * @return string
     */
    public function getTitle();
    public function getMerchantId();
    public function getMerchantName();

}
