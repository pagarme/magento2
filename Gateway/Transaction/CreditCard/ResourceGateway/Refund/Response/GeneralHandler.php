<?php
/**
 * Class GeneralHandler
 *
* @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\CreditCard\ResourceGateway\Refund\Response;


use Magento\Payment\Gateway\Response\HandlerInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\Response\AbstractHandler;
use Pagarme\Pagarme\Model\ChargesFactory;

class GeneralHandler extends AbstractHandler implements HandlerInterface
{
	/**
     * \Pagarme\Pagarme\Model\ChargesFactory
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this;
    }
}
