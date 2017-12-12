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

namespace MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\ResourceGateway\Refund\Response;


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
        $model = $this->modelCharges->create();
        $charge = $model->getCollection()->addFieldToFilter('charge_id',array('eq' => $response->id))->getFirstItem();
        try {
            $charge->setStatus($response->status);
            $charge->setPaidAmount($response->amount);
            $charge->setUpdatedAt(date("Y-m-d H:i:s"));
            $charge->save();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return $this;
    }
}
