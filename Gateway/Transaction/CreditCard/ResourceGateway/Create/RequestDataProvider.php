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

namespace MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use MundiPagg\MundiPagg\Api\CreditCardRequestDataProviderInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\ConfigInterface;
use MundiPagg\MundiPagg\Helper\CustomerAddressInterface;

class RequestDataProvider
    extends AbstractRequestDataProvider
    implements CreditCardRequestDataProviderInterface
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
    public function getInstallmentCount()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_installments');
    }

    /**
     * {@inheritdoc}
     */
    public function getSaveCard()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_savecard');
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardOperation()
    {
        if ($this->getConfig()->getPaymentAction()) {
            return \MundiPagg\MundiPagg\Model\Enum\CreditCardOperationEnum::AUTH_ONLY;
        }

        return \MundiPagg\MundiPagg\Model\Enum\CreditCardOperationEnum::AUTH_AND_CAPTURE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardBrand()
    {
        return $this->getBrandAdapter($this->getPaymentData()->getCcType());
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardNumber()
    {
        return $this->getPaymentData()->getCcNumber();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpMonth()
    {
        return $this->getPaymentData()->getCcExpMonth();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpYear()
    {
        return $this->getPaymentData()->getCcExpYear();
    }

    /**
     * {@inheritdoc}
     */
    public function getHolderName()
    {
        return $this->getPaymentData()->getCcOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityCode()
    {
        return $this->getPaymentData()->getCcCid();
    }

    /**
     * {@inheritdoc}
     */
    public function getIsOneDollarAuthEnabled()
    {
        return $this->getConfig()->getIsOneDollarAuthEnabled();
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
