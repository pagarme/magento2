<?php

namespace MundiPagg\MundiPagg\Model\Ui\Voucher;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\VoucherConfig;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup as MPSetup;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mundipagg_voucher';

    protected $voucherConfig;

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
        if (!empty($moduleConfig->getVoucherConfig())) {
            $this->setVoucherConfig($moduleConfig->getVoucherConfig());
        }
        $this->setCustomerSession($customerSession);
    }

    public function getConfig()
    {

        return [
            'payment' => [
                self::CODE =>[
                    'active' => $this->getVoucherConfig()->isEnabled(),
                    'title' => $this->getVoucherConfig()->getTitle(),
                    'size_credit_card' => '18',
                    'number_credit_card' => 'null',
                    'data_credit_card' => ''
                ]
            ]
        ];
    }

    /**
     * @return VoucherConfig
     */
    protected function getVoucherConfig()
    {
        return $this->voucherConfig;
    }

    /**
     * @param VoucherConfig $voucherConfig
     * @return $this
     */
    protected function setVoucherConfig(VoucherConfig $voucherConfig)
    {
        $this->voucherConfig = $voucherConfig;
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
