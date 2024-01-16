<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Magento\Framework\Exception\PaymentException;
use Magento\Sales\Model\Order\Payment;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Services\OrderLogService;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config as CreditCardConfig;
use Pagarme\Pagarme\Gateway\Transaction\DebitCard\Config\Config as DebitCardConfig;
use Pagarme\Pagarme\Model\Enum\CreditCardBrandEnum;
use Pagarme\Pagarme\Model\Ui\CreditCard\ConfigProvider as CreditConfigProvider;
use Pagarme\Pagarme\Model\Ui\Debit\ConfigProvider as DebitConfigProvider;

class ThreeDSService
{
    /**
     * @var CreditCardConfig
     */
    protected $creditCardConfig;

    /**
     * @param CreditCardConfig $creditCardConfig
     */
    public function __construct(CreditCardConfig $creditCardConfig)
    {
        $this->creditCardConfig = $creditCardConfig;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function hasThreeDSAuthorization(Payment $payment)
    {
        return $payment->getAdditionalInformation()['authentication'] ?? false;
    }

    /**
     * @param Payment $payment
     * @param PlatformOrderInterface $orderDecorator
     * @return void
     * @throws PaymentException
     */
    public function processDeclinedThreeDsTransaction(Payment $payment, PlatformOrderInterface $orderDecorator)
    {
        if ($this->isNotThreeDsPayment($payment) || $this->isAuthorizedThreeDsTransaction($payment)) {
            return;
        }

        if ($this->isOrderWithTdsRefusedDisabled($payment)) {
            $forceCreateOrder = MPSetup::getModuleConfiguration()->isCreateOrderEnabled();
            if ($forceCreateOrder) {
                $orderDecorator->setStatus(OrderStatus::canceled());
                $orderDecorator->setState(OrderState::canceled());
                $orderDecorator->save();

                $errorMessage = "Can't create payment. Please review the information and try again.";
                $logService = new OrderLogService();
                $logService->orderInfo(
                    $orderDecorator->getCode(),
                    $errorMessage
                );
                $logService->orderInfo(
                    $orderDecorator->getCode(),
                    "Failed to create order at Pagarme!"
                );

                $errorMessage = __(
                    "Order # %1 : %2",
                    $orderDecorator->getCode(),
                    __($errorMessage)
                );

                throw new PaymentException(__($errorMessage), null, 400);
            }


            throw new PaymentException(__("Order declined, do not try again."), null, 400);
        }
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    private function isNotThreeDsPayment(Payment $payment)
    {
        $status = $this->getThreeDsTransaction($payment);
        return ($payment->getMethod() !== CreditConfigProvider::CODE)
            || empty($status);
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    private function isAuthorizedThreeDsTransaction(Payment $payment)
    {
        $status = $this->getThreeDsTransaction($payment);
        $additionalInformation = $payment->getAdditionalInformation();
        $ccBrand = $additionalInformation['cc_type'] ?? '';
        $ccBrand = ucfirst(strtolower($ccBrand));
        return in_array($status, AuthenticationStatusEnum::doesNotNeedToUseAntifraudStatuses())
            && ($ccBrand === CreditCardBrandEnum::VISA
                || $ccBrand === CreditCardBrandEnum::MASTERCARD);
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    private function isOrderWithTdsRefusedDisabled(Payment $payment)
    {
        return $payment->getMethod() === CreditConfigProvider::CODE
                && !$this->creditCardConfig->getOrderWithTdsRefused();
    }

    /**
     * @param Payment $payment
     * @return string
     */
    private function getThreeDsTransaction(Payment $payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        $authentication = json_decode($additionalInformation['authentication'], true);
        return $authentication['trans_status'] ?? '';
    }
}
