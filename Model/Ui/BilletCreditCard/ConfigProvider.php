<?php
/**
 * Class ConfigProvider
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model\Ui\BilletCreditCard;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use MundiPagg\MundiPagg\Model\CardsFactory;
use MundiPagg\MundiPagg\Gateway\Transaction\BilletCreditCard\Config\ConfigInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mundipagg_billet_creditcard';

    protected $billetCreditCardConfig;

    protected $customerSession;

    protected $cardsFactory;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $billetCreditCardConfig
     */
    public function __construct(
        ConfigInterface $billetCreditCardConfig,
        Session $customerSession,
        CardsFactory $cardsFactory
    )
    {
        $this->setBilletCreditCardConfig($billetCreditCardConfig);
        $this->setCustomerSession($customerSession);
        $this->setCardsFactory($cardsFactory);
    }

    public function getConfig()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            $is_saved_card = 0;
            $selectedCard = '';
            $cards = [];
            $idCustomer = $this->getCustomerSession()->getCustomer()->getId();

            $model = $this->getCardsFactory();
            $cardsCollection = $model->getCollection()->addFieldToFilter('customer_id',array('eq' => $idCustomer));

            foreach ($cardsCollection as $card) {
                $is_saved_card = 1;
                $cards[] = [
                    'id' => $card->getId(),
                    'last_four_numbers' => $card->getLastFourNumbers(),
                ];
                $selectedCard = $card->getId();
            }

        }else{
            $is_saved_card = 0;
            $cards = [];
        }
        
        return [
            'payment' => [
                self::CODE =>[
                    'active' => $this->getBilletCreditCardConfig()->getActive(),
                    'is_saved_card' => $is_saved_card,
                    'cards' => $cards,
                    'selected_card' => $selectedCard
                ]
            ]
        ];
    }

    /**
     * @return ConfigInterface
     */
    protected function getBilletCreditCardConfig()
    {
        return $this->billetCreditCardConfig;
    }

    /**
     * @param ConfigInterface $billetCreditCardConfig
     * @return $this
     */
    protected function setBilletCreditCardConfig(ConfigInterface $billetCreditCardConfig)
    {
        $this->billetCreditCardConfig = $billetCreditCardConfig;
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
