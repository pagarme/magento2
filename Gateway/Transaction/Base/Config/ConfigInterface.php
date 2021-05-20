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

namespace Pagarme\Pagarme\Gateway\Transaction\Base\Config;


interface ConfigInterface
{
    const PATH_PUBLIC_KEY_TEST     = 'pagarme_pagarme/global/public_key_test';
    const PATH_SECRET_KEY_TEST     = 'pagarme_pagarme/global/secret_key_test';
    const PATH_PUBLIC_KEY          = 'pagarme_pagarme/global/public_key';
    const PATH_SECRET_KEY          = 'pagarme_pagarme/global/secret_key';
    const PATH_HUB_INSTALL_ID      = 'pagarme_pagarme/hub/install_id';
    const PATH_TEST_MODE           = 'pagarme_pagarme/global/test_mode';
    const PATH_SEND_EMAIL          = 'pagarme_pagarme/global/sendmail';
    const PATH_CUSTOMER_STREET     = 'payment/pagarme_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER     = 'payment/pagarme_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT = 'payment/pagarme_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT   = 'payment/pagarme_customer_address/district_attribute';

    /**
     * @return string
     */
    public function getSecretKey();

    /**
     * @return string
     */
    public function getPublicKey();

    /**
     * @return string
     */
    public function isHubEnabled();

    /**
     * @return string
     */
    public function getTestMode();

    /**
     * @return string
     */
    public function getBaseUrl();

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
     * @return bool
     */
    public function isSendEmail();
}
