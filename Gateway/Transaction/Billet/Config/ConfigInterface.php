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

namespace Pagarme\Pagarme\Gateway\Transaction\Billet\Config;


interface ConfigInterface
{
    const PATH_INSTRUCTIONS     = 'payment/pagarme_billet/instructions';
    const PATH_TEXT             = 'payment/pagarme_billet/text';
    const PATH_TYPE_BANK        = 'payment/pagarme_billet/types';
    const PATH_EXPIRATION_DAYS  = 'payment/pagarme_billet/expiration_days';
    const PATH_TITLE                        = 'payment/pagarme_billet/title';

    /**
     * @return string
     */
    public function getInstructions();

    /**
     * @return string
     */
    public function getText();

    /**
     * @return string
     */
    public function getTypeBank();

    /**
     * @return string
     */
    public function getExpirationDays();

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
