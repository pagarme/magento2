<?php

namespace Pagarme\Pagarme\Model\ObjectMapper\ProductSubscription;

use Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\RepetitionInterface;

class Repetition implements RepetitionInterface
{
  private $id;
  private $subscriptionId;
  private $interval;
  private $intervalCount;
  private $recurrencePrice;
  private $cycles;
  private $createdAt;
  private $updatedAt;

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  public function getSubscriptionId()
  {
    return $this->subscriptionId;
  }

  public function setSubscriptionId($subscriptionId)
  {
    $this->subscriptionId = $subscriptionId;
    return $this;
  }

  public function getInterval()
  {
    return $this->interval;
  }

  public function setInterval($interval)
  {
    $this->interval = $interval;
    return $this;
  }

  public function getIntervalCount()
  {
    return $this->intervalCount;
  }

  public function setIntervalCount($intervalCount)
  {
    $this->intervalCount = $intervalCount;
    return $this;
  }

  public function getRecurrencePrice()
  {
    return $this->recurrencePrice;
  }

  public function setRecurrencePrice($recurrencePrice)
  {
    $this->recurrencePrice = $recurrencePrice;
    return $this;
  }

  public function getCycles()
  {
    return $this->cycles;
  }

  public function setCycles($cycles)
  {
    $this->cycles = $cycles;
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
