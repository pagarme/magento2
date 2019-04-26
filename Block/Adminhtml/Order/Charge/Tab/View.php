<?php

namespace MundiPagg\MundiPagg\Block\Adminhtml\Order\Charge\Tab;

use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;

use Magento\Framework\UrlInterface;

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
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        Magento2CoreSetup::bootstrap();

        $this->_coreRegistry = $registry;
        $this->urlBuilder = $urlBuilder;

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
        $mundipaggOrder = (new OrderRepository)->findByPlatformId($platformOrderID);

        if ($mundipaggOrder === null) {
            return [];
        }

        $charges = (new ChargeRepository)->findByOrderId(
            new OrderId($mundipaggOrder->getMundipaggId()->getValue())
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
        return $this->urlBuilder->getUrl('mundipagg_mundipagg/charges/cancel');
    }

    public function getChargeCaptureUrl()
    {
        return $this->urlBuilder->getUrl('mundipagg_mundipagg/charges/capture');
    }
}