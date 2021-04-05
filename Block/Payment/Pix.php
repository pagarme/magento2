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

namespace Pagarme\Pagarme\Block\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface as Order;
use Magento\Sales\Api\Data\OrderPaymentInterface as Payment;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository as SubscriptionChargeRepository;

class Pix extends Template
{
    protected $checkoutSession;
    /**
     * Link constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(Context $context, CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, []);
    }

    /**
     * @return CheckoutSession
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return Order
     */
    protected function getLastOrder()
    {
        if (! ($this->checkoutSession->getLastRealOrder()) instanceof Order) {
            throw new \InvalidArgumentException;
        }
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @return Payment
     */
    protected function getPayment()
    {
        if (! ($this->getLastOrder()->getPayment()) instanceof Payment) {
            throw new \InvalidArgumentException;
        }
        return $this->getLastOrder()->getPayment();
    }

    /**
     * @return string
     */
    public function getPixInfo()
    {
        $info = $this->getPayment();
        $method = $info->getMethod();

        if (strpos($method, "pagarme_pix") === false) {
            return null;
        }

        $lastTransId = $info->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        Magento2CoreSetup::bootstrap();
        $orderService= new \Pagarme\Core\Payment\Services\OrderService();
        return $orderService->getPixQrCodeInfoFromOrder(new OrderId($orderId));
    }
}
