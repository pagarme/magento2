<?php
/**
 * Class AbstractHandler
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\Response;


use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @param $payment
     * @param $response
     * @return mixed
     */
    abstract protected function _handle($payment, $response);

    /**
     * {@inheritdoc}
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (
            ! isset($handlingSubject['payment']) ||
            ! $handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $response = $response['response'];
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();
        /** @TODO CREATE A BUILD RESPONSE */
        $this->_handle($payment, $response);
    }
}
