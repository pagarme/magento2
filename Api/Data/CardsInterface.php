<?php


namespace MundiPagg\MundiPagg\Api\Data;

interface CardsInterface
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CARD_TOKEN = 'card_token';
    const CARD_ID = 'card_id';
    const LAST_FOUR_NUMBERS = 'last_four_numbers';
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
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setId($id);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get card_token
     * @return string|null
     */
    public function getCardToken();

    /**
     * Set card_token
     * @param string $cardToken
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setCardToken($cardToken);

    /**
     * Get card_id
     * @return string|null
     */
    public function getCardId();

    /**
     * Set card_id
     * @param string $cardId
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setCardId($cardId);

    /**
     * Get last_four_numbers
     * @return string|null
     */
    public function getLastFourNumbers();

    /**
     * Set last_four_numbers
     * @param string $lastFourNumbers
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setLastFourNumbers($lastFourNumbers);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
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
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     */
    public function setUpdatedAt($updatedAt);
}
