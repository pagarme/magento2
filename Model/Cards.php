<?php


namespace Pagarme\Pagarme\Model;

use Pagarme\Pagarme\Api\Data\CardsInterface;
use Magento\Framework\Model\AbstractModel;

class Cards extends AbstractModel implements CardsInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Pagarme\Pagarme\Model\ResourceModel\Cards');
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get charges_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set card_owner
     * @param string $customerName
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function getCardHolderName()
    {
        return $this->getData(self::CARD_HOLDER_NAME);
    }

    /**
     * Set card_holder_name
     * @param string $cardToken
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCardHolderName($cardHolderName)
    {
        return $this->setData(self::CARD_HOLDER_NAME, $cardHolderName);
    }

    /**
     * Get card_token
     * @return string|null
     */
    public function getCardToken()
    {
        return $this->getData(self::CARD_TOKEN);
    }

    /**
     * Set card_token
     * @param string $cardToken
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCardToken($cardToken)
    {
        return $this->setData(self::CARD_TOKEN, $cardToken);
    }

    /**
     * Get card_id
     * @return string|NULL
     */
    public function getCardId()
    {
        return $this->getData(self::CARD_ID);
    }

    /**
     * Set card_id
     * @param string $cardId
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setCardId($cardId)
    {
        return $this->setData(self::CARD_ID, $cardId);
    }

    /**
     * Get last_four_numbers
     * @return string|null
     */
    public function getLastFourNumbers()
    {
        return $this->getData(self::LAST_FOUR_NUMBERS);
    }

    /**
     * Set last_four_numbers
     * @param string $lastFourNumbers
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setLastFourNumbers($lastFourNumbers)
    {
        return $this->setData(self::LAST_FOUR_NUMBERS, $lastFourNumbers);
    }

    /**
     * Get first_six_numbers
     * @return string|null
     */
    public function getFirstSixNumbers()
    {
        return $this->getData(self::FIRST_SIX_NUMBERS);
    }

    /**
     * Set first_six_numbers
     * @param string $lastFourNumbers
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setFirstSixNumbers($firstSixNumbers)
    {
        return $this->setData(self::FIRST_SIX_NUMBERS, $firstSixNumbers);
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
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
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get brand
     * @return string|null
     */
    public function getBrand()
    {
        return $this->getData(self::BRAND);
    }

    /**
     * Set brand
     * @param string $brand
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface
     */
    public function setBrand($brand)
    {
        return $this->setData(self::BRAND, $brand);
    }
}
