<?php
/**
 * Class GeneralHandler
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\ResourceGateway\Create\Response;


use Magento\Payment\Gateway\Response\HandlerInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\Response\AbstractHandler;
use MundiPagg\MundiPagg\Model\ChargesFactory;

class GeneralHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * \MundiPagg\MundiPagg\Model\ChargesFactory
     */
    protected $modelCharges;

    /**
     * @return void
     */
    public function __construct(
        ChargesFactory $modelCharges
    ) {
        $this->modelCharges = $modelCharges;
    }

    /**
     * {@inheritdoc}
     */
    protected function _handle($payment, $response)
    {
        $boletoUrl = $response->charges[0]->lastTransaction->pdf;

        $payment->setAdditionalInformation('billet_url', $boletoUrl);
        $payment->setTransactionId($response->id);
        $payment->setIsTransactionClosed(false);


        foreach($response->charges as $charge)
        {
            try {
                $model = $this->modelCharges->create();
                $model->setChargeId($charge->id);
                $model->setCode($charge->code);
                $model->setOrderId($payment->getOrder()->getIncrementId());
                $model->setType($charge->paymentMethod);
                $model->setStatus($charge->status);
                $model->setAmount($charge->amount);
                $model->setPaidAmount(0);
                $model->setRefundedAmount(0);
                $model->setCreatedAt(date("Y-m-d H:i:s"));
                $model->setUpdatedAt(date("Y-m-d H:i:s"));
                $model->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            
            
        }

        return $this;
    }
}
