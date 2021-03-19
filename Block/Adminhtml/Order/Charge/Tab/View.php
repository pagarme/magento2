<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Order\Charge\Tab;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;

class View  extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'tab/view/order_charge.phtml';

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        Magento2CoreSetup::bootstrap();

        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getCharges()
    {
        //@todo Create service to return the charges
        $platformOrderID = $this->getOrderIncrementId();
        $pagarmeOrder = (new OrderRepository)->findByPlatformId($platformOrderID);

        if ($pagarmeOrder === null) {
            return [];
        }

        $charges = (new ChargeRepository)->findByOrderId(
            new OrderId($pagarmeOrder->getPagarmeId()->getValue())
        );

        return $charges;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
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
        return __('Charges');
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
}
