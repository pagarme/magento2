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
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;

/**
 * Class RecurrenceProductsSubscription
 * @package Pagarme\Pagarme\Model
 */
class RecurrenceProductsSubscription extends AbstractModel implements RecurrenceProductsSubscriptionInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription::class);
    }

    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setProductId(int $productId): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return string|null
     */
    public function getCreditCard(): ?string
    {
        return $this->getData(self::CREDIT_CARD);
    }

    /**
     * @param string $creditCard
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setCreditCard(string $creditCard): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::CREDIT_CARD, $creditCard);
    }

    /**
     * @return string|null
     */
    public function getAllowInstallments(): ?string
    {
        return $this->getData(self::ALLOW_INSTALLMENTS);
    }

    /**
     * @param string $allowInstallments
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setAllowInstallments(string $allowInstallments): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::ALLOW_INSTALLMENTS, $allowInstallments);
    }

    /**
     * @return string|null
     */
    public function getBoleto(): ?string
    {
        return $this->getData(self::BOLETO);
    }

    /**
     * @param string $boleto
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setBoleto(string $boleto): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::BOLETO, $boleto);
    }

    /**
     * @return string|null
     */
    public function getSellAsNormalProduct(): ?string
    {
        return $this->getData(self::SELL_AS_NORMAL_PRODUCT);
    }

    /**
     * @param string $sellAsNormalProduct
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setSellAsNormalProduct(string $sellAsNormalProduct): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::SELL_AS_NORMAL_PRODUCT, $sellAsNormalProduct);
    }

    /**
     * @return string|null
     */
    public function getBillingType(): ?string
    {
        return $this->getData(self::BILLING_TYPE);
    }

    /**
     * @param string $billingType
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setBillingType(string $billingType): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::BILLING_TYPE, $billingType);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return RecurrenceProductsSubscriptionInterface
     */
    public function setUpdatedAt(string $updatedAt): \Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
