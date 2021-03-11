<?php
/**
 * Class Config
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Installments\Config;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\AbstractConfig;
use Magento\Checkout\Model\Session;

class ConfigByBrand extends AbstractConfig implements ConfigByBrandInterface
{
    protected $paymentMethodCode;
    protected $session;

    public function __construct(
        ScopeConfigInterface $storeConfig,
        Session $session,
        $paymentMethodCode = null
    )
    {
        parent::__construct($storeConfig);
        $this->setSession($session);
        $this->setPaymentMethodCode($paymentMethodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return (bool) $this->getConfig(self::PATH_INSTALLMENTS_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallmentUnique()
    {
        return (bool) $this->getConfig(self::PATH_INSTALLMENTS_UNIQUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallmentsNumber()
    {
        return (int) $this->getConfigByBrand(self::PATH_INSTALLMENTS_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function isWithInterest()
    {
        return (bool) $this->getConfigByBrand(self::PATH_INSTALLMENTS_IS_WITH_INTEREST);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallmentMinAmount()
    {
        return $this->getConfigByBrand(self::PATH_INSTALLMENTS_MIN_MOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterestRate()
    {
        return ((float) $this->getConfigByBrand(self::PATH_INSTALLMENTS_INTEREST_RATE) / 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterestRateIncremental()
    {
        return ((float) $this->getConfigByBrand(self::PATH_INSTALLMENTS_INTEREST_RATE_INCREMENTAL) / 100);
    }

    /**
     * {@inheritdoc}
     */
    public function isInterestByIssuer()
    {
        return (bool) $this->getConfigByBrand(self::PATH_INSTALLMENTS_INTEREST_BY_ISSUER);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallmentsMaxWithoutInterest()
    {
        return $this->getConfigByBrand(self::PATH_INSTALLMENTS_MAX_WITHOUT_INTEREST);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig($path, $store = null)
    {
        return parent::getConfig(sprintf($path, $this->getPaymentMethodCode()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigByBrand($path, $store = null)
    {
        return parent::getConfig(sprintf($path, $this->getPaymentMethodCode(), $this->session->getCardBrand()));
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return $this->paymentMethodCode;
    }

    /**
     * @param string $paymentMethodCode
     * @return $this
     */
    protected function setPaymentMethodCode($paymentMethodCode)
    {
        $this->paymentMethodCode = $paymentMethodCode;
        return $this;
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return $this
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }


}
