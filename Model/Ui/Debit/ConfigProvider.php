<?php

namespace Pagarme\Pagarme\Model\Ui\Debit;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Pagarme\Core\Kernel\ValueObjects\Configuration\DebitConfig;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup as MPSetup;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'pagarme_debit';

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

    private function getCardsCore()
    {
        $cards = [];

        $customerRepository = new CustomerRepository();
        $savedCardRepository = new SavedCardRepository();

        $idCustomer = $this->getCustomerSession()->getCustomer()->getId();
        $customer = $customerRepository->findByCode($idCustomer);

        if ($customer === null) {
            return $cards;
        }

        $coreCards = $savedCardRepository->findByOwnerId($customer->getPagarmeId());

        foreach ($coreCards as $coreCard) {
            $selectedCard = 'mp_core_' . $coreCard->getId();
            $cards[] = [
                'id' => $selectedCard,
                'first_six_digits' => $coreCard->getFirstSixDigits(),
                'last_four_numbers' => $coreCard->getLastFourDigits(),
                'brand' => $coreCard->getBrand()->getName(),
                'owner_name' => $coreCard->getOwnerName()
            ];
        }

        return $cards;
    }

    public function getConfig()
    {
        $selectedCard = '';
        $isSavedCard = 0;
        $cards = [];

        if ($this->getCustomerSession()->isLoggedIn()) {
            $cards = $this->getCardsCore();
        }

        if (!empty($cards)) {
            $selectedCard = (end($cards))['id'];
            $isSavedCard = 1;
        }

        return [
            'payment' => [
                self::CODE =>[
                    'active' => $this->getDebitConfig()->isEnabled(),
                    'title' => $this->getDebitConfig()->getTitle(),
                    'size_credit_card' => '18',
                    'number_credit_card' => 'null',
                    'data_credit_card' => '',
                    'enabled_saved_cards' => $this->getDebitConfig()->isSaveCards(),
                    'is_saved_card' => $isSavedCard,
                    'cards' => $cards,
                    'selected_card' => $selectedCard,
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
