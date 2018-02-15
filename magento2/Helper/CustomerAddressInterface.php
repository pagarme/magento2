<?php
/**
 * Class CustomerAddressInterface
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Helper;


interface CustomerAddressInterface
{
    const PATH_STREET_ATTRIBUTE         = 'mundipagg/mundipagg_customer_address/street_attribute';
    const PATH_NUMBER_ATTRIBUTE         = 'mundipagg/mundipagg_customer_address/number_attribute';
    const PATH_COMPLEMENT_ATTRIBUTE     = 'mundipagg/mundipagg_customer_address/complement_attribute';
    const PATH_DISTRICT_ATTRIBUTE       = 'mundipagg/mundipagg_customer_address/district_attribute';

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
