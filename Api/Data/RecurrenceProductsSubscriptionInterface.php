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
 * Class RecurrenceProductsSubscriptionInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface RecurrenceProductsSubscriptionInterface
{
    /** @var string */
    const CREDIT_CARD = 'credit_card';

    /** @var string */
    const RECURRENCE_PRODUCTS_SUBSCRIPTION_ID = 'recurrence_products_subscription_id';

    /** @var string */
    const ALLOW_INSTALLMENTS = 'allow_installments';

    /** @var string */
    const UPDATED_AT = 'updated_at';

    /** @var string */
    const ID = 'id';

    /** @var string */
    const PRODUCT_ID = 'product_id';

    /** @var string */
    const SELL_AS_NORMAL_PRODUCT = 'sell_as_normal_product';

    /** @var string */
    const CREATED_AT = 'created_at';

    /** @var string */
    const BOLETO = 'boleto';

    /** @var string */
    const BILLING_TYPE = 'billing_type';

    /**
     * Get product_id
     * @return int|null
     */
    public function getProductId(): ?int;

    /**
     * Set product_id
     * @param int $productId
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setProductId(int $productId): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get credit_card
     * @return string|null
     */
    public function getCreditCard(): ?string;

    /**
     * Set credit_card
     * @param string $creditCard
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setCreditCard(string $creditCard): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get allow_installments
     * @return string|null
     */
    public function getAllowInstallments(): ?string;

    /**
     * Set allow_installments
     * @param string $allowInstallments
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setAllowInstallments(string $allowInstallments): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get boleto
     * @return string|null
     */
    public function getBoleto(): ?string;

    /**
     * Set boleto
     * @param string $boleto
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setBoleto(string $boleto): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get sell_as_normal_product
     * @return string|null
     */
    public function getSellAsNormalProduct(): ?string;

    /**
     * Set sell_as_normal_product
     * @param string $sellAsNormalProduct
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setSellAsNormalProduct(string $sellAsNormalProduct): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get billing_type
     * @return string|null
     */
    public function getBillingType(): ?string;

    /**
     * Set billing_type
     * @param string $billingType
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setBillingType(string $billingType): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
     */
    public function setUpdatedAt(string $updatedAt): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;
}
