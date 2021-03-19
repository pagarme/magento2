<?php
/**
 * Class ConfigProvider
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Ui\TwoCreditCard;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\CardsFactory;
use Pagarme\Pagarme\Gateway\Transaction\TwoCreditCard\Config\ConfigInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'pagarme_two_creditcard';

    protected $creditCardConfig;

    protected $customerSession;

    protected $cardsFactory;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $creditCardConfig
     */
    public function __construct(
        Session $customerSession,
        ConfigInterface $creditCardConfig,
        CardsFactory $cardsFactory
    )
    {
        $this->setCreditCardConfig($creditCardConfig);
        $this->setCustomerSession($customerSession);
        $this->setCardsFactory($cardsFactory);
    }

    public function getConfig()
    {
        $selectedCard = '';
        $cards = [];

        $is_saved_card = 0;
        if ($this->getCustomerSession()->isLoggedIn()) {
            $cards = [];
            $idCustomer = $this->getCustomerSession()->getCustomer()->getId();

            $model = $this->getCardsFactory();
            $cardsCollection = $model->getCollection()->addFieldToFilter('customer_id', array('eq' => $idCustomer));

            foreach ($cardsCollection as $card) {
                $is_saved_card = 1;
                $cards[] = [
                    'id' => $card->getId(),
                    'last_four_numbers' => $card->getLastFourNumbers(),
                    'brand' => $card->getBrand(),
                ];
                $selectedCard = $card->getId();
            }

            Magento2CoreSetup::bootstrap();

            $customerRepository = new CustomerRepository();
            $savedCardRepository = new SavedCardRepository();

            $customer = $customerRepository->findByCode($idCustomer);
            if ($customer !== null) {
                $coreCards =
                    $savedCardRepository->findByOwnerId($customer->getPagarmeId());

                foreach ($coreCards as $coreCard) {
                    $is_saved_card = 1;
                    $selectedCard = 'mp_core_' . $coreCard->getId();

                    $cards[] = [
                        'id' => $selectedCard,
                        'first_six_digits' => $coreCard->getFirstSixDigits(),
                        'last_four_numbers' => $coreCard->getLastFourDigits(),
                        'brand' => $coreCard->getBrand()->getName()
                    ];
                }
            }
        }

        return [
            'payment' => [
                'ccform' => [
                    'availableTypes' =>
                        [
                            self::CODE => $this->getCreditCardsBrands()
                        ],
                ],
                self::CODE => [
                    'active' => $this->getCreditCardConfig()->getActive(),
                    'title' => $this->getCreditCardConfig()->getTitle(),
                    'is_saved_card' => $is_saved_card,
                    'enabled_saved_cards' => MPSetup::getModuleConfiguration()->isSaveCards(),
                    'cards' => $cards,
                    'selected_card' => $selectedCard,
                    'size_credit_card' => '18',
                    'number_credit_card' => 'null',
                    'data_credit_card' => ''
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getCreditCardsBrands()
    {
        $listCardConfig = MPSetup::getModuleConfiguration()->getCardConfigs();

        $brands = [];
        foreach ($listCardConfig as $cardConfig) {
            if (!$cardConfig->isEnabled()) {
                continue;
            }

            if ($cardConfig->getBrand()->getName() == 'noBrand') {
                continue;
            }

            $brands[$cardConfig->getBrand()->getName()] = $cardConfig->getBrand()->getName();
        }

        return $brands;
    }

    /**
     * @return ConfigInterface
     */
    protected function getCreditCardConfig()
    {
        return $this->creditCardConfig;
    }

    /**
     * @param ConfigInterface $creditCardConfig
     * @return $this
     */
    protected function setCreditCardConfig(ConfigInterface $creditCardConfig)
    {
        $this->creditCardConfig = $creditCardConfig;
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

    /**
     * @return mixed
     */
    public function getCardsFactory()
    {
        return $this->cardsFactory->create();
    }

    /**
     * @param mixed $cardsFactory
     *
     * @return self
     */
    public function setCardsFactory($cardsFactory)
    {
        $this->cardsFactory = $cardsFactory;

        return $this;
    }
}
