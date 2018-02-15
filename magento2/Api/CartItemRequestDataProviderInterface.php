<?php
/**
 * Class CartItemRequestProvider
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Api;


interface CartItemRequestDataProviderInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getItemReference();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @return float
     */
    public function getUnitCostInCents();

    /**
     * @return float
     */
    public function getTotalCostInCents();
}
