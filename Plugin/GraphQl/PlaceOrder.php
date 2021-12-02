<?php
declare(strict_types=1);

namespace Pagarme\Pagarme\Plugin\GraphQl;

use Magento\Sales\Api\OrderRepositoryInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class PlaceOrder
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
    }

    public function afterResolve(
        \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder $subject,
        $result
    ) {
        $order = $this->orderFactory->create()->loadByIncrementId($result['order']['order_number']);
        $payment = $order->getPayment();


        if (strpos($payment->getMethod(), "pagarme_pix") === false) {
            return $result;
        }

        $lastTransId = $payment->getLastTransId();
        $orderId = substr($lastTransId, 0, 19);

        Magento2CoreSetup::bootstrap();
        $orderService= new \Pagarme\Core\Payment\Services\OrderService();
        $result['pagarme_pix'] = $orderService->getPixQrCodeInfoFromOrder(new OrderId($orderId));

        return $result;
    }
}