<?php


namespace Pagarme\Pagarme\Api\Data;

interface CardsInterface
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CARD_TOKEN = 'card_token';
    const CARD_ID = 'card_id';
    const CARD_HOLDER_NAME = 'card_holder_name';
    const LAST_FOUR_NUMBERS = 'last_four_numbers';
    const FIRST_SIX_NUMBERS = 'first_six_numbers';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const BRAND = 'brand';

    /**
     * Get id
     * @return int
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get card_holder_name
     * @return string|null
     */
    public function getCardHolderName();

    /**
     * Set card_holder_name
     * @param string $cardHolderName
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCardHolderName($cardHolderName);

    /**
     * Get card_token
     * @return string|null
     */
    public function getCardToken();

    /**
     * Set card_token
     * @param string $cardToken
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setLastFourNumbers($lastFourNumbers);

    /**
     * Get first_six_numbers
     * @return string|null
     */
    public function getFirstSixNumbers();

    /**
     * Set first_six_numbers
     * @param string $firstSixNumbers
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setFirstSixNumbers($firstSixNumbers);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get grand
     * @return string|null
     */
    public function getBrand();

    /**
     * Set brand
     * @param string $brand
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setBrand($brand);
}
