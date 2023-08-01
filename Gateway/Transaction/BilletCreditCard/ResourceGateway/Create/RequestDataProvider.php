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

namespace Pagarme\Pagarme\Gateway\Transaction\BilletCreditCard\ResourceGateway\Create;


use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Pagarme\Pagarme\Api\BilletCreditCardRequestDataProviderInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractRequestDataProvider;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\ConfigInterface;
use Pagarme\Pagarme\Helper\CustomerAddressInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\AbstractAddressDataProvider;

class RequestDataProvider
    extends AbstractAddressDataProvider
    implements BilletCreditCardRequestDataProviderInterface
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
    public function getSaveCard()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_savecard');
    }

    /**
     * {@inheritdoc}
     */
    public function getBilletBuyerName()
    {
        return $this->getPaymentData()->getAdditionalInformation('billet_buyer_name');
    }

    /**
     * {@inheritdoc}
     */
    public function getBilletBuyerEmail()
    {
        return $this->getPaymentData()->getAdditionalInformation('billet_buyer_email');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcBuyerName()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_buyer_name');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcBuyerEmail()
    {
        return $this->getPaymentData()->getAdditionalInformation('cc_buyer_email');
    }

    /**
     * {@inheritdoc}
     */
    public function getBilletCreditCardOperation()
    {
        if ($this->getConfig()->getPaymentAction()) {
            return \Pagarme\Pagarme\Model\Enum\CreditCardOperationEnum::AUTH_ONLY;
        }

        return \Pagarme\Pagarme\Model\Enum\CreditCardOperationEnum::AUTH_AND_CAPTURE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBilletCreditCardBrand()
    {
        return $this->getPaymentData()->getCcType();
    }

    /**
     * {@inheritdoc}
     */
    public function getBilletCreditCardNumber()
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
    public function getCcCcAmount()
    {
        return $this->getPaymentData()->getCcCcAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getCcBilletAmount()
    {
        return $this->getPaymentData()->getCcBilletAmount();
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
