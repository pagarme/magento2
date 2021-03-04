<?php
/**
 * Class CustomerAddressInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Helper;


interface CustomerAddressInterface
{
    const PATH_STREET_ATTRIBUTE         = 'pagarme/pagarme_customer_address/street_attribute';
    const PATH_NUMBER_ATTRIBUTE         = 'pagarme/pagarme_customer_address/number_attribute';
    const PATH_COMPLEMENT_ATTRIBUTE     = 'pagarme/pagarme_customer_address/complement_attribute';
    const PATH_DISTRICT_ATTRIBUTE       = 'pagarme/pagarme_customer_address/district_attribute';

    /**
     * @return string
     */
    public function getStreetAttribute();

    /**
     * @return string
     */
    public function getNumberAttribute();

    /**
     * @return string
     */
    public function getComplementAttribute();

    /**
     * @return string
     */
    public function getDistrictAttribute();
}
