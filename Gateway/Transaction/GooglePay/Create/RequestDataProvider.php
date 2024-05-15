<?php
/**
 * Class RequestDataProvider
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\GooglePay\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface;
use Pagarme\Pagarme\Helper\CustomerAddressInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractAddressDataProvider;

class RequestDataProvider extends AbstractAddressDataProvider
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct (
        OrderAdapterInterface $orderAdapter,
        InfoInterface $payment,
        Session $session,
        CustomerAddressInterface $customerAddressHelper,
        ConfigInterface $config
    )
    {
        parent::__construct($orderAdapter, $payment, $session, $customerAddressHelper);
        $this->setConfig($config);
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    protected function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

}
