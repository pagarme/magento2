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

use Magento\Payment\Block\Info\Cc;

use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class BilletCreditCard extends Cc
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/billetCreditCard.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getInfo()->getAdditionalInformation('billet_url')
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    public function getCcType()
    {
        return $this->getCcTypeName();
    }

    public function getCardNumber()
    {
        return '**** **** **** ' . $this->getInfo()->getCcLast4();
    }

    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    public function getBilletUrl()
    {
        $method = $this->getInfo()->getMethod();

        if (strpos($method, "mundipagg_billet") === false) {
            return;
        }

        $boletoUrl = $this->getInfo()->getAdditionalInformation('billet_url');

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

    public function getCcAmount()
    {
        return $this->getInfo()->getAdditionalInformation('cc_cc_amount');
    }

    public function getCcAmountWithTax()
    {
        return $this->getInfo()->getAdditionalInformation('cc_cc_amount') + $this->getInfo()->getAdditionalInformation('cc_cc_tax_amount') / 100;
    }

    public function getBilletAmount()
    {
        return $this->getInfo()->getAdditionalInformation('cc_billet_amount');
    }
}