<?php
declare(strict_types=1);

namespace Pagarme\Pagarme\Plugin\GraphQl;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
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

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterResolve(
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