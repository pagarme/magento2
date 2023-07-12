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

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info\Cc;
use Magento\Payment\Model\Config;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Helper\NumberFormatHelper;
use Pagarme\Pagarme\Helper\Payment\Billet as BilletHelper;

class BilletCreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/billetCreditCard.phtml';

    /**
     * @var NumberFormatHelper
     */
    private $numberFormatter;

    public function __construct(
        Context $context,
        Config $paymentConfig,
        NumberFormatHelper $numberFormatter,
        array $data = []
    ) {
        $this->numberFormatter = $numberFormatter;
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null): DataObject
    {
        $specificInformation = new DataObject([
            (string)__('Print Billet') => $this->getInfo()->getAdditionalInformation('billet_url')
        ]);

        return parent::_prepareSpecificInformation($specificInformation);
    }

    /**
     * @throws Exception
     */
    protected function _construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * @throws LocalizedException
     */
    public function getCardLast4(): string
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    /**
     * @throws LocalizedException
     */
    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    /**
     * @throws LocalizedException
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    /**
     * @throws LocalizedException
     */
    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    /**
     * @throws LocalizedException
     */
    public function getBilletUrl()
    {
        $billetHelper = new BilletHelper();
        return $billetHelper->getBilletUrl($this->getInfo());

    }

    /**
     * @throws LocalizedException
     */
    public function getCcAmountWithTax()
    {
        $ccAmountWithTax = (float) $this->getInfo()->getAdditionalInformation('cc_cc_amount') +
        (float) $this->getInfo()->getAdditionalInformation('cc_cc_tax_amount');
        return $this->numberFormatter->formatToLocalCurrency($ccAmountWithTax);
    }

    /**
     * @throws LocalizedException
     */
    public function getBilletAmount()
    {
        return $this->numberFormatter->formatToLocalCurrency(
            $this->getInfo()->getAdditionalInformation('cc_billet_amount')
        );
    }

    /**
     * @throws InvalidParamException
     * @throws LocalizedException
     */
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
         * @var Order $orderObject
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

    public function getCreditCardInformation(): string
    {
        $creditCardInformation = '';
        if (empty($this->getCcTypeName())) {
            return $creditCardInformation;
        }

        $creditCardInformation .= sprintf('<p>%s</p>', __($this->getTitle()));
        $creditCardInformation .= sprintf(
            '<strong class="box-title"><span>%s</span></strong>',
            __('Credit Card')
        );
        $creditCardInformation .= $this->formatCreditCardData(__('Amount'), $this->getCcAmountWithTax());
        $creditCardInformation .= $this->formatCreditCardData(__('Brand'), $this->getCcBrand());
        $creditCardInformation .= $this->formatCreditCardData(__('Number'), $this->getCardLast4());
        $creditCardInformation .= $this->formatCreditCardData(__('Installments'), $this->getInstallments());

        return $creditCardInformation;
    }

    /**
     * @throws LocalizedException
     */
    public function getPrintBillet(): string
    {
        $printBillet = '';

        $canShowPrintBillet = !empty($this->getBilletUrl()) && $this->getInfo()->getOrder()->getStatus() === 'pending';
        if (!$canShowPrintBillet) {
            return $printBillet;
        }

        $printBillet .= sprintf(
            '<a class="action tocart primary" id="pagarme-link-boleto" href="%s" target="_blank">%s</a>',
            $this->getBilletUrl(),
            __('Print Billet')
        );
        return $printBillet;
    }

    private function formatCreditCardData($title, $information): string
    {
        return sprintf('<p><b>%s: </b>%s</p>', $title, $information);
    }

    private function getTid(Charge $charge): ?string
    {
        $transaction = $charge->getLastTransaction();

        $tid = null;
        if ($transaction !== null) {
            $tid = $transaction->getAcquirerTid();
        }

        return $tid;
    }
}
