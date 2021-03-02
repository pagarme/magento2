<?php


namespace Pagarme\Pagarme\Api\Data;

interface ChargesInterface
{
    const ID = 'id';
    const CHARGE_ID = 'charge_id';
    const CODE = 'code';
    const ORDER_ID = 'order_id';
    const TYPE = 'type';
    const STATUS = 'status';
    const AMOUNT = 'amount';
    const PAID_AMOUNT = 'paid_amount';
    const REFUNDED_AMOUNT = 'refunded_amount';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get id
     * @return int
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setId($id);

    /**
     * Get charge_id
     * @return string|null
     */
    public function getChargeId();

    /**
     * Set charge_id
     * @param string $charges_id
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setChargeId($chargeId);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setCode($code);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setOrderId($orderId);

    /**
     * Get type
     * @return string|null
     */
    public function getType();

    /**
     * Set type
     * @param string $type
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setType($type);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setStatus($status);

    /**
     * Get amount
     * @return float|null
     */
    public function getAmount();

    /**
     * Set amount
     * @param float $amount
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setAmount($amount);

    /**
     * Get paid_amount
     * @return float|null
     */
    public function getPaidAmount();

    /**
     * Set paid_amount
     * @param float $paidAmount
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setPaidAmount($paidAmount);

    /**
     * Get refunded_amount
     * @return float|null
     */
    public function getRefundedAmount();

    /**
     * Set refunded_amount
     * @param float $refundedAmount
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setRefundedAmount($refundedAmount);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface
     */
    public function setUpdatedAt($updatedAt);
}
