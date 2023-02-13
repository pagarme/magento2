<?php
/**
 * Class Billet
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Payment\Info;

use Magento\Framework\DataObject;
use Magento\Payment\Block\Info\Cc;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Helper\Payment\Billet as BilletHelper;

class BilletCreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/billetCreditCard.phtml';

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
        Magento2CoreSetup::bootstrap();
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
        $billetHelper = new BilletHelper();
        return $billetHelper->getBilletUrl($this->getInfo());

    }

    public function getCcAmount()
    {
        return $this->getInfo()->getAdditionalInformation('cc_cc_amount');
    }

    public function getCcAmountWithTax()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_cc_amount') + (float)$this->getInfo()->getAdditionalInformation('cc_cc_tax_amount');
    }

    public function getBilletAmount()
    {
        return (float)$this->getInfo()->getAdditionalInformation('cc_billet_amount');
    }

    public function getTransactionInfo()
    {
        $orderService = new OrderService();

        $orderEntityId = $this->getInfo()->getOrder()->getIncrementId();

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();
        if ($orderPagarmeId === null) {
            return [];
        }

        /**
         * @var \Pagarme\Core\Kernel\Aggregates\Order orderObject
         */
        $orderObject = $orderService->getOrderByPagarmeId(new OrderId($orderPagarmeId));

        if ($orderObject === null) {
            return [];
        }

        $lastTransaction = $orderObject->getCharges()[0]->getLastTransaction();
        $secondLastTransaction = $orderObject->getCharges()[1]->getLastTransaction();

        $transactionList = [];
        foreach ([$lastTransaction, $secondLastTransaction] as $item) {
            if ($item->getAcquirerNsu() != 0) {
                $transactionList['creditCard'] =
                    array_merge(
                        $orderObject->getCharges()[0]->getAcquirerTidCapturedAndAutorize(),
                        ['tid' => $this->getTid($orderObject->getCharges()[0])]
                    );

                continue;
            }

            $transactionList['billet'] = $item;
        }

        return $transactionList;
    }

    private function getTid(Charge $charge)
    {
        $transaction = $charge->getLastTransaction();

        $tid = null;
        if ($transaction !== null) {
            $tid = $transaction->getAcquirerTid();
        }

        return $tid;
    }
}
