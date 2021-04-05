<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Payment\Services\ValidationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;


class OrderPlaceBeforeObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        Magento2CoreSetup::bootstrap();

        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if (strpos($payment->getMethod(), 'pagarme') === false) {
            return;
        }

        $platformOrderDecoratorClass = Magento2CoreSetup::get(
            Magento2CoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS
        );

        /** @var PlatformOrderInterface $orderDecorator */
        $orderDecorator = new $platformOrderDecoratorClass();
        $orderDecorator->setPlatformOrder($order);

        return $this->validate($orderDecorator);
    }

    protected function validate(PlatformOrderInterface $order)
    {
        $validationService = new ValidationService();
        $validationService->validatePayment($order);

        foreach ($validationService->getErrors() as $error) {
            throw new InputException(__($error));
        }

        return true;
    }
}
