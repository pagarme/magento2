<?php
/**
 * Class CreateBilletCreditCardDataProviderInterface
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Api;


interface BilletCreditCardRequestDataProviderInterface extends BaseRequestDataProviderInterface
{

    /**
     * @return int
     */
    public function getBankType();

    /**
     * @return int
     */
    public function getInstallmentCount();

    /**
     * @return int
     */
    public function getSaveCard();
    
    /**
     * @return string
     */
    public function getBilletCreditCardOperation();

    /**
     * @return string
     */
    public function getBilletCreditCardBrand();

    /**
     * @return string
     */
    public function getBilletCreditCardNumber();

    /**
     * @return string
     */
    public function getExpMonth();

    /**
     * @return string
     */
    public function getExpYear();

    /**
     * @return string
     */
    public function getHolderName();

    /**
     * @return string
     */
    public function getSecurityCode();

    /**
     * @return string
     */
    public function getIsOneDollarAuthEnabled();
    
    /**
     * @return string
     */
    public function getCustomerAddressStreet($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressNumber($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressComplement($shipping);

    /**
     * @return string
     */
    public function getCustomerAddressDistrict($shipping);
}
