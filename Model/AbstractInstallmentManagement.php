<?php

namespace Pagarme\Pagarme\Model;

use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\InstallmentService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

abstract class AbstractInstallmentManagement
{
    public function __construct()
    {
    }

    /**
     * @param Order|null $order
     * @param CardBrand|null $brand
     * @param null $value
     * @return array
     * @throws InvalidParamException
     */
    protected function getCoreInstallments(
        Order $order = null,
        CardBrand $brand = null,
        $value = null
    ) {
        Magento2CoreSetup::bootstrap();
        $installmentService = new InstallmentService();
        $moneyService = new MoneyService();

        $installments = $installmentService->getInstallmentsFor(
            $order,
            $brand,
            $value * 100
        );

        $result = [];
        foreach ($installments as $installment) {
            $result[] = [
                'id' => $installment->getTimes(),
                'interest' =>
                    $moneyService->centstoFloat(
                        $moneyService->floatToCents(
                            ($installment->getTotal() - $installment->getBaseTotal()) / 100
                        )
                    ),
                'total_with_tax' => $moneyService->centstoFloat(
                    $moneyService->floatToCents(
                        $installment->getTotal() / 100
                    )
                ),
                'label' => $installmentService->getLabelFor($installment)
            ];
        }

        return $result;
    }
}
