<?php


namespace MundiPagg\MundiPagg\Model;

use MundiPagg\MundiPagg\Api\Data\ChargesInterface;
use Magento\Framework\Model\AbstractModel;

class Charges extends AbstractModel implements ChargesInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MundiPagg\MundiPagg\Model\ResourceModel\Charges');
    }

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id
     * @param int $id
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get charges_id
     * @return string
     */
    public function getChargeId()
    {
        return $this->getData(self::CHARGE_ID);
    }

    /**
     * Set charges_id
     * @param string $chargesId
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setChargeId($chargeId)
    {
        return $this->setData(self::CHARGE_ID, $chargeId);
    }

    /**
     * Get code
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Set code
     * @param string $code
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get type
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type
     * @param string $type
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get amount
     * @return float|null
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set amount
     * @param float $amount
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get paid_amount
     * @return float|null
     */
    public function getPaidAmount()
    {
        return $this->getData(self::PAID_AMOUNT);
    }

    /**
     * Set paid_amount
     * @param float $paidAmount
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setPaidAmount($paidAmount)
    {
        return $this->setData(self::PAID_AMOUNT, $paidAmount);
    }

    /**
     * Get refunded_amount
     * @return float|null
     */
    public function getRefundedAmount()
    {
        return $this->getData(self::REFUNDED_AMOUNT);
    }

    /**
     * Set refunded_amount
     * @param float $refundedAmount
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setRefundedAmount($refundedAmount)
    {
        return $this->setData(self::REFUNDED_AMOUNT, $refundedAmount);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
