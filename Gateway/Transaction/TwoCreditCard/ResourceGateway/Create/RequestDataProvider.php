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

namespace Pagarme\Pagarme\Gateway\Transaction\TwoCreditCard\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Pagarme\Pagarme\Api\CreditCardRequestDataProviderInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use Pagarme\Pagarme\Gateway\Transaction\TwoCreditCard\Config\ConfigInterface;
use Pagarme\Pagarme\Helper\CustomerAddressInterface;

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
    public function getCcTokenCreditCard()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_token_credit_card');
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenCreditCardFirst()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_token_credit_card_first');
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenCreditCardSecond()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_token_credit_card_second');
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
    public function getCcBuyerNameFirst()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_buyer_name_first');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcBuyerEmailFirst()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_buyer_email_first');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcBuyerNameSecond()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_buyer_name_second');
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardOperation()
    {
        if ($this->getConfig()->getPaymentAction()) {
            return \Pagarme\Pagarme\Model\Enum\CreditCardOperationEnum::AUTH_ONLY;
        }

        return \Pagarme\Pagarme\Model\Enum\CreditCardOperationEnum::AUTH_AND_CAPTURE;
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
            $response = !$this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? '' : $this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressComplement());
        }else{
            $response = !$this->getBillingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? '' : $this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressComplement());
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getCustomerAddressDistrict($shipping)
    {
        if ($shipping) {
            $streetLine = !$this->getShippingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? 'street_3' : $this->getConfig()->getCustomerAddressDistrict();
            $response = $this->getShippingAddressAttribute($streetLine);
        }else{
            $streetLine = !$this->getBillingAddressAttribute($this->getConfig()->getCustomerAddressDistrict()) ? 'street_3' : $this->getConfig()->getCustomerAddressDistrict();
            $response = $this->getBillingAddressAttribute($streetLine);
        }

        return $response;
    }

}
