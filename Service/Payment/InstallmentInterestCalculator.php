<?php

namespace Pagarme\Pagarme\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use Pagarme\Core\Kernel\Services\InstallmentService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;

class InstallmentInterestCalculator
{
    /**
     * @param string|null $brand
     * @param int $installments
     * @param int $baseAmountInCents
     * @return int
     * @throws LocalizedException
     */
    public function calculateInterestInCents($brand, $installments, $baseAmountInCents)
    {
        $cardBrand = $this->createCardBrand($brand);

        foreach ($this->getInstallments($cardBrand, (int) $baseAmountInCents) as $installment) {
            if ($installment->getTimes() === (int) $installments) {
                return (int) round($installment->getTotal() - $installment->getBaseTotal());
            }
        }

        throw new LocalizedException(__('Invalid installment number!'));
    }

    /**
     * @param CardBrand $brand
     * @param int $baseAmountInCents
     * @return \Pagarme\Core\Kernel\ValueObjects\Installment[]
     */
    protected function getInstallments(CardBrand $brand, $baseAmountInCents)
    {
        return (new InstallmentService())->getInstallmentsFor(null, $brand, $baseAmountInCents);
    }

    /**
     * @param string|null $brand
     * @return CardBrand
     * @throws LocalizedException
     */
    private function createCardBrand($brand)
    {
        $brand = strtolower((string) $brand);
        $isStaticFactory = $brand !== '' && method_exists(CardBrand::class, $brand);

        if (!$isStaticFactory) {
            throw new LocalizedException(__('Invalid credit card brand'));
        }

        return CardBrand::$brand();
    }
}
