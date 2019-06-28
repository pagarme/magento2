<?php
/**
 * Class InitializedCommand
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\Command;


use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\OrderService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Model\Ui\CreditCard\ConfigProvider;
use MundiPagg\MundiPagg\Model\Ui\TwoCreditCard\ConfigProvider as TwoCreditCardConfigProvider;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as M2WebApiException;

class InitializeCommand implements CommandInterface
{
    /**
     * @param array $commandSubject
     * @return $this
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Framework\DataObject $stateObject */
        $stateObject = $commandSubject['stateObject'];
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            throw new \LogicException('Order Payment should be provided');
        }
        $orderResult = $this->doCoreDetour($payment);
        if ($orderResult !== false) {
            $orderResult->loadByIncrementId(
                $orderResult->getIncrementId()
            );

            $stateObject->setData(
                OrderInterface::STATE,
                $orderResult->getState()->getState()
            );
            $stateObject->setData(
                OrderInterface::STATUS,
                $orderResult->getStatus()
            );
            return $this;
        }

        $payment->getOrder()->setCanSendNewEmailFlag(true);
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();
        $totalDue = $payment->getOrder()->getTotalDue();
        $payment->authorize(true, $baseTotalDue);
        $payment->setAmountAuthorized($totalDue);
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());
        $customStatus = $payment->getData('custom_status');

        $stateObject->setData(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);

        if ($payment->getMethod() === ConfigProvider::CODE || $payment->getMethod() === TwoCreditCardConfigProvider::CODE) {
            $stateObject->setData(OrderInterface::STATE, $customStatus->getData('state'));
            $stateObject->setData(OrderInterface::STATUS, $customStatus->getData('status'));
        }

        if ($payment->getMethod() != ConfigProvider::CODE) {
            $stateObject->setData(OrderInterface::STATUS, $payment->getMethodInstance()->getConfigData('order_status'));
        }

        $stateObject->setData('is_notified', false);

        return $this;
    }

     /** @return AbstractPlatformOrderDecorator */
    private function doCoreDetour($payment)
    {
        try {
            $order =  $payment->getOrder();

            Magento2CoreSetup::bootstrap();

            $platformOrderDecoratorClass = MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS
            );

            /** @var PlatformOrderInterface $orderDecorator */
            $orderDecorator = new $platformOrderDecoratorClass();
            $orderDecorator->setPlatformOrder($order);


            $orderService = new OrderService();
            $orderService->createOrderAtMundipagg($orderDecorator);
            
            $orderDecorator->save();

            return $orderDecorator;
        } catch (\Exception $e) {
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                $e->getCode()
            );
        }
    }
}