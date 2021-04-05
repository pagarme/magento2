<?php
/**
 * Class InstallmentInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Api\Data;


interface InstallmentInterface
{
    const QTY   = 'qty';
    const LABEL = 'label';
    const PRICE = 'price';
    const HAS_INTEREST = 'has_interest';
    const GRAND_TOTAL = 'grand_total';
    const INTEREST = 'interest';

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param int $value
     * @return self
     */
    public function setQty($value);

    /**
     * @return float
     */
    public function getInterest();

    /**
     * @param float $value
     * @return self
     */
    public function setInterest($value);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $value
     * @return string
     */
    public function setLabel($value);

    /**
     * @return bool
     */
    public function getHasInterest();

    /**
     * @param bool $value
     * @return self
     */
    public function setHasInterest($value);

    /**
     * @param bool $format
     * @param bool $includeContainer
     * @return string
     */
    public function getPrice($format = true, $includeContainer = true);

    /**
     * @param float $value
     * @return self
     */
    public function setPrice($value);

    /**
     * @param float $value
     * @return self
     */
    public function setGrandTotal($value);

    /**
     * @param bool $format
     * @param bool $includeContainer
     * @return string
     */
    public function getGrandTotal($format = true, $includeContainer = true);
}
