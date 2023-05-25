<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Model\AbstractModel;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class SavedCard
 * @package Pagarme\Pagarme\Model
 */
class SavedCard extends AbstractModel implements SavedCardInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Pagarme\Pagarme\Model\ResourceModel\SavedCard::class);
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string|null $type
     * @return SavedCardInterface
     */
    public function setType(?string $type): SavedCardInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getPagarmeId(): string
    {
        return $this->getData(self::PAGARME_ID);
    }

    /**
     * @param string $pagarmeId
     * @return SavedCardInterface
     */
    public function setPagarmeId(string $pagarmeId): SavedCardInterface
    {
        return $this->setData(self::PAGARME_ID, $pagarmeId);
    }

    /**
     * @return string
     */
    public function getOwnerId(): string
    {
        return $this->getData(self::OWNER_ID);
    }

    /**
     * @param string $ownerId
     * @return SavedCardInterface
     */
    public function setOwnerId(string $ownerId): SavedCardInterface
    {
        return $this->setData(self::OWNER_ID, $ownerId);
    }

    /**
     * @return string
     */
    public function getFirstSixDigits(): string
    {
        return $this->getData(self::FIRST_SIX_DIGITS);
    }

    /**
     * @param string $firstSixDigits
     * @return SavedCardInterface
     */
    public function setFirstSixDigits(string $firstSixDigits): SavedCardInterface
    {
        return $this->setData(self::FIRST_SIX_DIGITS, $firstSixDigits);
    }

    /**
     * @return string
     */
    public function getLastFourDigits(): string
    {
        return $this->getData(self::LAST_FOUR_DIGITS);
    }

    /**
     * @param string $lastFourDigits
     * @return SavedCardInterface
     */
    public function setLastFourDigits(string $lastFourDigits): SavedCardInterface
    {
        return $this->setData(self::LAST_FOUR_DIGITS, $lastFourDigits);
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->getData(self::BRAND);
    }

    /**
     * @param string $brand
     * @return SavedCardInterface
     */
    public function setBrand(string $brand): SavedCardInterface
    {
        return $this->setData(self::BRAND, $brand);
    }

    /**
     * @return string|null
     */
    public function getOwnerName(): ?string
    {
        return $this->getData(self::OWNER_NAME);
    }

    /**
     * @param string|null $ownerName
     * @return SavedCardInterface
     */
    public function setOwnerName(?string $ownerName): SavedCardInterface
    {
        return $this->setData(self::OWNER_NAME, $ownerName);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return SavedCardInterface
     */
    public function setCreatedAt(string $createdAt): SavedCardInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
