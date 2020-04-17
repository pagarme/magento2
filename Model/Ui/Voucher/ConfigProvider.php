<?php

namespace MundiPagg\MundiPagg\Model\Ui\Voucher;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\VoucherConfig;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup as MPSetup;
use MundiPagg\MundiPagg\Model\CardsFactory;

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
        Session $customerSession,
        CardsFactory $cardsFactory
    )
    {
        MPSetup::bootstrap();
        $moduleConfig = MPSetup::getModuleConfiguration();
        if (!empty($moduleConfig->getVoucherConfig())) {
            $this->setVoucherConfig($moduleConfig->getVoucherConfig());
        }
        $this->setCustomerSession($customerSession);
        $this->cardsFactory = $cardsFactory;
    }

    public function getConfig()
    {
        $selectedCard = '';
        $is_saved_card = 0;
        $cards = [];

        if ($this->getCustomerSession()->isLoggedIn()) {

            $idCustomer = $this->getCustomerSession()->getCustomer()->getId();

            $model = $this->cardsFactory->create();
            $cardsCollection = $model->getCollection()->addFieldToFilter('customer_id',array('eq' => $idCustomer));

            foreach ($cardsCollection as $card) {
                $is_saved_card = 1;
                $cards[] = [
                    'id' => $card->getId(),
                    'last_four_numbers' => $card->getLastFourNumbers(),
                    'brand' => $card->getBrand()
                ];
                $selectedCard = $card->getId();
            }

            Magento2CoreSetup::bootstrap();

            $customerRepository = new CustomerRepository();
            $savedCardRepository = new SavedCardRepository();

            $customer = $customerRepository->findByCode($idCustomer);
            if ($customer !== null) {
                $coreCards =
                    $savedCardRepository->findByOwnerId($customer->getMundipaggId());

                foreach ($coreCards as $coreCard) {
                    $is_saved_card = 1;
                    $selectedCard = 'mp_core_' . $coreCard->getId();

                    $cards[] = [
                        'id' => $selectedCard,
                        'first_six_digits' => $coreCard->getFirstSixDigits(),
                        'last_four_numbers' => $coreCard->getLastFourDigits(),
                        'brand' => $coreCard->getBrand()->getName(),
                        'owner_name' => $coreCard->getOwnerName()
                    ];
                }
            }
        }

        return [
            'payment' => [
                self::CODE =>[
                    'active' => $this->getVoucherConfig()->isEnabled(),
                    'title' => $this->getVoucherConfig()->getTitle(),
                    'size_credit_card' => '18',
                    'number_credit_card' => 'null',
                    'data_credit_card' => '',
                    'enabled_saved_cards' => $this->getVoucherConfig()->isSaveCards(),
                    'is_saved_card' => $is_saved_card,
                    'cards' => $cards,
                    'selected_card' => $selectedCard,
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
