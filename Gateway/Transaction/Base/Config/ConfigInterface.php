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
    const PATH_ENABLED = 'pagarme_pagarme/global/active';

    const PATH_PUBLIC_KEY_TEST = 'pagarme_pagarme/global/public_key_test';

    const PATH_SECRET_KEY_TEST = 'pagarme_pagarme/global/secret_key_test';

    const PATH_PUBLIC_KEY = 'pagarme_pagarme/global/public_key';

    const PATH_SECRET_KEY = 'pagarme_pagarme/global/secret_key';

    const PATH_HUB_INSTALL_ID = 'pagarme_pagarme/hub/install_id';

    const PATH_HUB_ENVIRONMENT = 'pagarme_pagarme/hub/environment';

    const PATH_TEST_MODE = 'pagarme_pagarme/global/test_mode';

    const PATH_SEND_EMAIL = 'pagarme_pagarme/global/sendmail';

    const PATH_CUSTOMER_VAT_NUMBER = 'customer/create_account/vat_frontend_visibility';

    const PATH_CUSTOMER_ADDRESS_LINES = 'customer/address/street_lines';

    const PATH_CUSTOMER_ADDRESS_STREET = 'payment/pagarme_customer_address/street_attribute';

    const PATH_CUSTOMER_ADDRESS_NUMBER = 'payment/pagarme_customer_address/number_attribute';

    const PATH_CUSTOMER_ADDRESS_COMPLEMENT = 'payment/pagarme_customer_address/complement_attribute';

    const PATH_CUSTOMER_ADDRESS_NEIGHBOURHOOD = 'payment/pagarme_customer_address/district_attribute';

    const HUB_SANDBOX_ENVIRONMENT = 'Sandbox';

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getSecretKey();

    /**
     * @return bool
     */
    public function isSandboxMode(): bool;

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
     * @return array
     */
    public function getPagarmeCustomerConfigs();

    /**
     * @return array
     */
    public function getPagarmeCustomerAddressConfigs();

    /**
     * @return bool
     */
    public function isSendEmail();
}
