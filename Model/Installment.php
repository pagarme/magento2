<?php
/**
 * Class Installment
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model;


use Magento\Framework\DataObject;
use Pagarme\Pagarme\Api\Data\InstallmentInterface;
use Magento\Framework\Pricing\Helper\Data;

class Installment extends DataObject implements InstallmentInterface
{
    protected $priceHelper;

    public function __construct(
        Data $priceHelper,
        array $data = []
    )
    {
        parent::__construct($data);
        $this->setPriceHelper($priceHelper);
    }


    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(static::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($value)
    {
        return $this->setData(static::QTY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterest()
    {
        return $this->getData(static::INTEREST);
    }

    /**
     * {@inheritdoc}
     */
    public function setInterest($value)
    {
        return $this->setData(static::INTEREST, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(static::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($value)
    {
        return $this->setData(static::LABEL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getHasInterest()
    {
        return $this->getData(static::HAS_INTEREST);
    }

    /**
     * {@inheritdoc}
     */
    public function setHasInterest($value)
    {
        return $this->setData(static::HAS_INTEREST, $value);

    }

    /**
     * {@inheritdoc}
     */
    public function getPrice($format = true, $includeContainer = true)
    {
        return $this->getPriceHelper()->currency(
            $this->getData(static::PRICE),
            $format,
            $includeContainer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($value)
    {
        return $this->setData(static::PRICE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getGrandTotal($format = true, $includeContainer = true)
    {
        return $this->getPriceHelper()->currency(
            $this->getData(static::GRAND_TOTAL),
            $format,
            $includeContainer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setGrandTotal($value)
    {
        return $this->setData(static::GRAND_TOTAL, $value);
    }

    /**
     * @return Data
     */
    protected function getPriceHelper()
    {
        return $this->priceHelper;
    }

    /**
     * @param Data $priceHelper
     * @return self
     */
    protected function setPriceHelper(Data $priceHelper)
    {
        $this->priceHelper = $priceHelper;
        return $this;
    }
}
