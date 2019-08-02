<?php

namespace MundiPagg\MundiPagg\Concrete;

use Mundipagg\Core\Payment\Aggregates\SavedCard;

final class Magento2SavedCardAdapter
{
    private $adaptee;

    public function __construct(SavedCard $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    public function getBrand()
    {
        return $this->adaptee->getBrand()->getName();
    }

    public function getLastFourNumbers()
    {
        return $this->adaptee->getLastFourDigits()->getValue();
    }

    public function getCreatedAt()
    {
        $createdAt = $this->adaptee->getCreatedAt();
        if ($createdAt !== null) {
            return $createdAt->format(SavedCard::DATE_FORMAT);
        }

        return null;
    }

    public function getId()
    {
        return 'mp_core_' . $this->adaptee->getId();
    }

    public function getFirstSixDigits()
    {
        return $this->adaptee->getFirstSixDigits()->getValue();
    }

    public function getMaskedNumber()
    {
        $firstSix = $this->getFirstSixDigits();
        $lastFour = $this->getLastFourNumbers();

        $firstSix = number_format($firstSix/100, 2, '.', '');

        return $firstSix . '**.****.' . $lastFour;
    }
}
