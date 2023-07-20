<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Order\Charge\Tab;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\HtmlTableHelper;
use Pagarme\Pagarme\Service\Order\ChargeService;

class View  extends Template implements TabInterface
{
    // @codingStandardsIgnoreLine
    protected $_template = 'tab/view/order_charge.phtml';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var HtmlTableHelper
     */
    private $htmlTableHelper;

    /**
     * @var ChargeService
     */
    private $chargeService;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param HtmlTableHelper $htmlTableHelper
     * @param ChargeService $chargeService
     * @param array $data
     * @throws Exception
     */
    public function __construct(
        Context         $context,
        Registry        $registry,
        HtmlTableHelper $htmlTableHelper,
        ChargeService   $chargeService,
        array           $data = []
    ) {
        Magento2CoreSetup::bootstrap();

        $this->registry = $registry;
        $this->htmlTableHelper = $htmlTableHelper;
        $this->chargeService = $chargeService;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getChargesTableBody()
    {
        $tbody = '';

        foreach ($this->getCharges() as $charge) {
            $tbody .= '<tr>';
            $tbody .= $this->htmlTableHelper->formatTableDataCell($charge->getPagarmeId()->getValue());
            $tbody .= $this->htmlTableHelper->formatNumberTableDataCell($charge->getAmount());
            $tbody .= $this->htmlTableHelper->formatNumberTableDataCell($charge->getPaidAmount());
            $tbody .= $this->htmlTableHelper->formatNumberTableDataCell($charge->getCanceledAmount());
            $tbody .= $this->htmlTableHelper->formatNumberTableDataCell($charge->getRefundedAmount());
            $tbody .= $this->htmlTableHelper->formatTableDataCell($charge->getStatus()->getStatus());
            $tbody .= $this->htmlTableHelper->formatTableDataCell($this->getAmountInput($charge), 'amount');
            $tbody .= $this->getCaptureChargeButtonDataCell($charge);
            $tbody .= $this->getCancelChargeButtonDataCell($charge);
            $tbody .= '</tr>';
        }

        return $tbody;
    }

    /**
     * @param Charge $charge
     * @return string
     */
    public function getAmountInput($charge)
    {
        return sprintf('<input class="amount-value" value="%s" />', $charge->getAmount());
    }

    /**
     * @param Charge $charge
     * @return string
     */
    public function getCaptureChargeButtonDataCell($charge)
    {
        $buttonTableDataCell = '';

        if ($charge->getCanceledAmount() <= 0) {
            $button = $this->getActionChargeButton(
                $charge,
                'capture',
                __('Do you want to capture this charge?'),
                __('Capture')
            );
            $buttonTableDataCell .= $this->htmlTableHelper->formatTableDataCell($button);
        }

        return $buttonTableDataCell;
    }

    /**
     * @param Charge $charge
     * @return string
     */
    public function getCancelChargeButtonDataCell($charge)
    {
        $button = $this->getActionChargeButton(
            $charge,
            'cancel',
            __('Do you want to cancel this charge?'),
            __('Cancel')
        );
        return $this->htmlTableHelper->formatTableDataCell($button);
    }

    /**
     * @param Charge $charge
     * @param string $action
     * @param string $message
     * @param string $label
     * @return string
     */
    public function getActionChargeButton($charge, $action, $message, $label)
    {
        return sprintf(
            '<button class="action charge-button" data-action="%s" data-order="%s"' .
            ' data-charge="%s" data-message="%s">%s</button>',
            $action,
            $charge->getOrderId()->getValue(),
            $charge->getPagarmeId()->getValue(),
            $message,
            $label
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Charges');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function getChargeCancelUrl()
    {
        return $this->_urlBuilder->getUrl('pagarme_pagarme/charges/cancel');
    }

    public function getChargeCaptureUrl()
    {
        return $this->_urlBuilder->getUrl('pagarme_pagarme/charges/capture');
    }

    /**
     * Retrieve order model instance
     *
     * @return Order
     */
    private function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return array
     * @throws InvalidParamException
     */
    private function getCharges()
    {
        return $this->chargeService->findChargesByIncrementId(
            $this->getOrderIncrementId()
        );
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    private function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
}
