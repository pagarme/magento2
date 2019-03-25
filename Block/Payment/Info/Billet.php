<?php
/**
 * Class Billet
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Payment\Info;


use Magento\Payment\Block\Info;
use Magento\Framework\DataObject;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class Billet extends Info
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/billet.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getBilletUrl()
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    public function getBilletUrl()
    {
        $boletoUrl =  $this->getInfo()->getAdditionalInformation('billet_url');

        Magento2CoreSetup::bootstrap();
        $info = $this->getInfo();
        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByMundipaggId(new OrderId($orderId));

        if ($order !== null) {
            $charges = $order->getCharges();
            foreach ($charges as $charge) {
                $transaction = $charge->getLastTransaction();
                $savedBoletoUrl = $transaction->getBoletoUrl();
                if ($savedBoletoUrl !== null) {
                    $boletoUrl = $savedBoletoUrl;
                }
            }
        }

        return $boletoUrl;
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }
}