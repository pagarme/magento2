<?php

namespace MundiPagg\MundiPagg\Model\Ui\Debit;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\DebitConfig;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup as MPSetup;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mundipagg_debit';

    protected $debitConfig;

    protected $customerSession;

    protected $cardsFactory;

    /**
     * ConfigProvider constructor.
     * @param Session $customerSession
     * @throws \Exception
     */
    public function __construct(
        Session $customerSession
    )
    {
        MPSetup::bootstrap();
        $moduleConfig = MPSetup::getModuleConfiguration();
        if (!empty($moduleConfig->getDebitConfig())) {
            $this->setDebitConfig($moduleConfig->getDebitConfig());
        }
        $this->setCustomerSession($customerSession);
    }

    public function getConfig()
    {

        return [
            'payment' => [
                self::CODE =>[
                    'active' => $this->getDebitConfig()->isEnabled(),
                    'title' => $this->getDebitConfig()->getTitle(),
                    'size_credit_card' => '18',
                    'number_credit_card' => 'null',
                    'data_credit_card' => ''
                ]
            ]
        ];
    }

    /**
     * @return DebitConfig
     */
    protected function getDebitConfig()
    {
        return $this->debitConfig;
    }

    /**
     * @param DebitConfig $debitConfig
     * @return $this
     */
    protected function setDebitConfig(DebitConfig $debitConfig)
    {
        $this->debitConfig = $debitConfig;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @param mixed $customerSession
     *
     * @return self
     */
    public function setCustomerSession($customerSession)
    {
        $this->customerSession = $customerSession;

        return $this;
    }
}
