<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Order\Charge\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Pagarme\Block\BaseTemplateWithCurrency;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Service\Order\ChargeService;

class View  extends BaseTemplateWithCurrency implements TabInterface
{
    // @codingStandardsIgnoreLine
    protected $_template = 'tab/view/order_charge.phtml';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ChargeService
     */
    private $chargeService;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $priceHelper
     * @param ChargeService $chargeService
     * @param array $data
     * @throws Exception
     */
    public function __construct(
        Context         $context,
        Registry        $registry,
        Data            $priceHelper,
        ChargeService   $chargeService,
        array           $data = []
    ) {
        Magento2CoreSetup::bootstrap();

        $this->registry = $registry;
        $this->chargeService = $chargeService;

        parent::__construct($context, $priceHelper, $data);
    }

    /**
     * @return array
     * @throws InvalidParamException
     */
    public function getCharges()
    {
        return $this->chargeService->findChargesByIncrementId(
            $this->getOrderIncrementId()
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
     * Retrieve order increment id
     *
     * @return string
     */
    private function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
}
