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

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config;


interface ConfigInterface
{
    const PATH_INSTRUCTIONS     = 'payment/mundipagg_billet/instructions';
    const PATH_TEXT             = 'payment/mundipagg_billet/text';
    const PATH_TYPE_BANK        = 'payment/mundipagg_billet/types';
    const PATH_EXPIRATION_DAYS  = 'payment/mundipagg_billet/expiration_days';
    const PATH_CUSTOMER_STREET              = 'payment/mundipagg_customer_address/street_attribute';
    const PATH_CUSTOMER_NUMBER              = 'payment/mundipagg_customer_address/number_attribute';
    const PATH_CUSTOMER_COMPLEMENT          = 'payment/mundipagg_customer_address/complement_attribute';
    const PATH_CUSTOMER_DISTRICT            = 'payment/mundipagg_customer_address/district_attribute';
    const PATH_TITLE                        = 'payment/mundipagg_billet/title';

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
