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

namespace Pagarme\Pagarme\Gateway\Transaction\Billet\ResourceGateway\Create;

use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractAddressDataProvider;
use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Pagarme\Pagarme\Api\BilletRequestDataProviderInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use Pagarme\Pagarme\Gateway\Transaction\Billet\Config\ConfigInterface;
use Pagarme\Pagarme\Helper\CustomerAddressInterface;

class RequestDataProvider
    extends AbstractAddressDataProvider
    implements BilletRequestDataProviderInterface
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
     * {@inheritdoc}
     */
    public function getBankType()
    {
        return $this->getConfig()->getTypeBank();
    }

    /**
     * {@inheritdoc}
     */
    public function getInstructions()
    {
        return $this->getConfig()->getInstructions();
    }

    /**
     * {@inheritdoc}
     */
    public function getDaysToAddInBoletoExpirationDate()
    {
        return $this->getConfig()->getExpirationDays();
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
    protected function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

}
