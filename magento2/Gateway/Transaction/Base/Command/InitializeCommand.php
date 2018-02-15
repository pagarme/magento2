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
use MundiPagg\MundiPagg\Model\Ui\CreditCard\ConfigProvider;

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

        $payment->getOrder()->setCanSendNewEmailFlag(true);
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();
        $totalDue = $payment->getOrder()->getTotalDue();
        $payment->authorize(true, $baseTotalDue);
        $payment->setAmountAuthorized($totalDue);
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());
        $stateObject->setData(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);

        if ($payment->getMethod() === ConfigProvider::CODE) {
            $stateObject->setData(OrderInterface::STATE, Order::STATE_PROCESSING);
        }

        $stateObject->setData(OrderInterface::STATUS, $payment->getMethodInstance()->getConfigData('order_status'));

        if ($payment->getIsFraudDetected()) {
            $stateObject->setData(OrderInterface::STATE, Order::STATE_PAYMENT_REVIEW);
            $stateObject->setData(OrderInterface::STATUS, $payment->getMethodInstance()->getConfigData('reject_order_status'));
        }

        if ($payment->getIsTransactionPending()) {
            $stateObject->setData(OrderInterface::STATE, Order::STATE_PAYMENT_REVIEW);
            $stateObject->setData(OrderInterface::STATUS, $payment->getMethodInstance()->getConfigData('review_order_status'));
        }

        $stateObject->setData('is_notified', false);

        return $this;
    }
}
