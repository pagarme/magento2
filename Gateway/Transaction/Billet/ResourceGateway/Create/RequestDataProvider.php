<?php
/**
 * Class RequestDataProvider
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use MundiPagg\MundiPagg\Api\BilletRequestDataProviderInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config\ConfigInterface;
use MundiPagg\MundiPagg\Helper\CustomerAddressInterface;

class RequestDataProvider
    extends AbstractRequestDataProvider
    implements BilletRequestDataProviderInterface
{
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
