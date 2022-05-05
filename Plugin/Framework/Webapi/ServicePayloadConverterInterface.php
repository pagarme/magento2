<?php
/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Plugin\Framework\Webapi;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * class ServicePayloadConverterInterface
 * @package Pagarme\Pagarme\Plugin\Framework\Webapi
 */
class ServicePayloadConverterInterface
{
    /** @var string */
    const ORDERINTERFACE_CLASS = '\Magento\Sales\Api\Data\OrderInterface';

    /** @var string */
    const PAGARME_TRANSACTION_ID_FIELD = 'last_trans_id';

    /** @var string */
    const PAGARME_TRANSACTION_ID = 'pagarme_transaction_id';

    /** @var OrderRepositoryInterface */
    private OrderRepositoryInterface $_orderRepository;

    /** @var Logger */
    private Logger $logger;

    /**
     * @param OrderRepositoryInterface $_orderRepository
     * @param Logger $logger
     */
    public function __construct(
        OrderRepositoryInterface $_orderRepository,
        Logger $logger
    ) {
        $this->_orderRepository = $_orderRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Webapi\ServicePayloadConverterInterface $subject
     * @param $result
     * @param $data
     * @param string $type
     * @return array|mixed
     */
    public function afterConvertValue(
        \Magento\Framework\Webapi\ServicePayloadConverterInterface $subject,
        $result,
        $data,
        string $type
    ) {
        if ($type === self::ORDERINTERFACE_CLASS) {
            $result = $this->setPargarmeTransactionId($result);
        }
        return $result;
    }

    /**
     * @param array $result
     * @return array
     */
    private function setPargarmeTransactionId(array $result): array
    {
        try {
            $order = $this->_orderRepository->get($result[OrderInterface::ENTITY_ID]);
            if ($lastTransId = $order->getPayment()->getData(self::PAGARME_TRANSACTION_ID_FIELD)) {
                $exploded = explode('-', $lastTransId);
                $result['payment'][self::PAGARME_TRANSACTION_ID] = array_shift($exploded);
            }
        } catch (InputException | NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }
}
