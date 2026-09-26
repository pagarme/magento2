<?php

namespace Pagarme\Pagarme\Model\ObjectMapper\ProductSubscription;

use Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface;

class ProductSubscriptionMapper implements ProductSubscriptionMapperInterface
{
  /** @var int|null */
  protected $id;

  /** @var int|null */
  protected $productId;

  /** @var bool */
  protected $creditCard = false;

  /** @var bool */
  protected $boleto = false;

  /** @var bool */
  protected $allowInstallments = false;

  /** @var bool */
  protected $sellAsNormalProduct = false;

  /** @var \Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\RepetitionInterface[]|null */
  protected $repetitions;

  /** @var \DateTime|null */
  protected $createdAt;

  /** @var \DateTime|null */
  protected $updatedAt;

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  public function getProductId()
  {
    return $this->productId;
  }

  public function setProductId($productId)
  {
    $this->productId = $productId;
    return $this;
  }

  public function getCreditCard()
  {
    return $this->creditCard;
  }

  public function setCreditCard($creditCard)
  {
    $this->creditCard = (bool) $creditCard;
    return $this;
  }

  public function getBoleto()
  {
    return $this->boleto;
  }

  public function setBoleto($boleto)
  {
    $this->boleto = (bool) $boleto;
    return $this;
  }

  public function getAllowInstallments()
  {
    return $this->allowInstallments;
  }

  public function setAllowInstallments($installments)
  {
    $this->allowInstallments = (bool) $installments;
    return $this;
  }

  public function getRepetitions()
  {
    return $this->repetitions;
  }

  public function setRepetitions(array $repetitions)
  {
    $this->repetitions = $repetitions;
    return $this;
  }

  public function getSellAsNormalProduct()
  {
    return $this->sellAsNormalProduct;
  }

  public function setSellAsNormalProduct($sellAsNormalProduct)
  {
    $this->sellAsNormalProduct = (bool) $sellAsNormalProduct;
    return $this;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTime $createdAt)
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(\DateTime $updatedAt)
  {
    $this->updatedAt = $updatedAt;
    return $this;
  }
}
