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

namespace MundiPagg\MundiPagg\Gateway\Transaction\TwoCreditCard\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use MundiPagg\MundiPagg\Api\CreditCardRequestDataProviderInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use MundiPagg\MundiPagg\Gateway\Transaction\TwoCreditCard\Config\ConfigInterface;
use MundiPagg\MundiPagg\Helper\CustomerAddressInterface;

class RequestDataProvider
    extends AbstractRequestDataProvider
    implements CreditCardRequestDataProviderInterface
{
    protected $config;

    public function __construct(
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
    public function getSaveCardFirst()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_savecard_first');
    }

    /**
     * {@inheritdoc}
     */
    public function getSaveCardSecond()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_savecard_second');
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
        return $this->getPaymentData()->getCcType();
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

    /**
     * @return string
     */
    public function getCustomerAddressStreet($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerStreetAttribute());
        }

        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerStreetAttribute());
    }

    /**
     * @return string
     */
    public function getCustomerAddressNumber($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerNumberAttribute());
        }
        
        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerNumberAttribute());
    }

    /**
     * @return string
     */
    public function getCustomerAddressComplement($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerComplementAttribute());
        }
        
        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerComplementAttribute());
    }

    /**
     * @return string
     */
    public function getCustomerAddressDistrict($shipping)
    {
        if ($shipping) {
            return $this->getShippingAddressAttribute($this->getConfig()->getCustomerDistrictAttribute());
        }
        
        return $this->getBillingAddressAttribute($this->getConfig()->getCustomerDistrictAttribute());
    }
}
