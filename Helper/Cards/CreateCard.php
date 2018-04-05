<?php 

/**
 * Class CreateCard
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Helper\Cards;

use MundiPagg\MundiPagg\Model\CardsFactory;

class CreateCard
{
	private $cardsFactory;

	/**
     * CreateCard constructor.
     * @param CardsFactory $cardsFactory
     */
    public function __construct(
        CardsFactory $cardsFactory
    )
    {
        $this->setCardsFactory($cardsFactory);
    }

    public function getById($cardsId)
    {
    	$cards = $this->getCardsFactory();
    	$cards->getResource()->load($cards, $cardsId);
        if (!$cards->getId()) {
            throw new NoSuchEntityException(__('Cards with id "%1" does not exist.', $cardsId));
        }

        return $cards;
    }

    public function createCard($card, $customer, $quote)
    {
        try {
            $cards = $this->getCardsFactory();
            $cards->setCustomerId($quote->getCustomerId());
            $cards->setCardToken($card->id);
            $cards->setCardId($customer->id);
            $cards->setLastFourNumbers($card->lastFourDigits);
            $cards->setBrand($card->brand);
            $cards->setCreatedAt(date("Y-m-d H:i:s"));
            $cards->setUpdatedAt(date("Y-m-d H:i:s"));
            $cards->save();
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());
        }

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