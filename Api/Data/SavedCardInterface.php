<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Api\Data;

/**
 * Class SavedCardInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface SavedCardInterface
{
    /** @var string */
    const FIRST_SIX_DIGITS = 'first_six_digits';

    /** @var string */
    const TYPE = 'type';

    /** @var string */
    const OWNER_ID = 'owner_id';

    /** @var string */
    const PAGARME_ID = 'pagarme_id';

    /** @var string */
    const LAST_FOUR_DIGITS = 'last_four_digits';

    /** @var string */
    const BRAND = 'brand';

    /** @var string */
    const CREATED_AT = 'created_at';

    /** @var string */
    const OWNER_NAME = 'owner_name';

    /** @var string */
    const ENTITY_ID = 'id';

    /**
     * Get type
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set type
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): SavedCardInterface;

    /**
     * Get pagarme_id
     * @return string
     */
    public function getPagarmeId(): string;

    /**
     * Set pagarme_id
     * @param string $pagarmeId
     * @return $this
     */
    public function setPagarmeId(string $pagarmeId): SavedCardInterface;

    /**
     * Get owner_id
     * @return string
     */
    public function getOwnerId(): string;

    /**
     * Set owner_id
     * @param string $ownerId
     * @return $this
     */
    public function setOwnerId(string $ownerId): SavedCardInterface;

    /**
     * Get first_six_digits
     * @return string
     */
    public function getFirstSixDigits(): string;

    /**
     * Set first_six_digits
     * @param string $firstSixDigits
     * @return $this
     */
    public function setFirstSixDigits(string $firstSixDigits): SavedCardInterface;

    /**
     * Get last_four_digits
     * @return string
     */
    public function getLastFourDigits(): string;

    /**
     * Set last_four_digits
     * @param string $lastFourDigits
     * @return $this
     */
    public function setLastFourDigits(string $lastFourDigits): SavedCardInterface;

    /**
     * Get brand
     * @return string
     */
    public function getBrand(): string;

    /**
     * Set brand
     * @param string $brand
     * @return $this
     */
    public function setBrand(string $brand): SavedCardInterface;

    /**
     * Get owner_name
     * @return string|null
     */
    public function getOwnerName(): ?string;

    /**
     * Set owner_name
     * @param string|null $ownerName
     * @return $this
     */
    public function setOwnerName(?string $ownerName): SavedCardInterface;

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set created_at
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): SavedCardInterface;
}
