<?php
/**
 * Class Cards
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2019 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Adminhtml\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mundipagg\Core\Payment\Aggregates\SavedCard;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2SavedCardAdapter;
use MundiPagg\MundiPagg\Model\CardsRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\Session;

class Cards extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CardsRepository
     */
    protected $cardsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteria;

    /**
     * Link constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        CardsRepository $cardsRepository,
        SearchCriteriaBuilder $criteria
    ) {
        parent::__construct($context, []);
        $this->setCardsRepository($cardsRepository);
        $this->setCriteria($criteria);
    }

    private function addFilterCriteria($fieldName, $fieldValue, $filterType = 'eq')
    {
        $searchCriteria = $this->getCriteria()
            ->addFilter($fieldName, $fieldValue, $filterType)->create();
        return $searchCriteria;
    }

    /**
     * @return array|Magento2SavedCardAdapter[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCardsList()
    {
        $searchCriteria = $this->addFilterCriteria(
            'id',
            '',
            'notnull'
        );

        $listCards = $this->getCardsRepository()->getList($searchCriteria);

        /* @var \MundiPagg\MundiPagg\Model\Cards[] $cards */
        $cards = $listCards->getItems();
        foreach ($cards as &$card) {
            $card->setMaskedNumber('****.****.****.' . $card->getLastFourNumbers());
        }

        $cards = array_values($cards);
       

        return array_merge($cards, $this->getCoreCards());
    }

    /**
     * @return array|SavedCard[]
     * @throws \Exception
     */
    private function getCoreCards()
    {
        Magento2CoreSetup::bootstrap();
        
        $savedCardRepository = new SavedCardRepository();
        $customerRepository = new CustomerRepository();

        $listSavedCoreCard = $savedCardRepository->listEntities(0, false);
       
        /* @var Magento2SavedCardAdapter[]|array $cards */
        $cards = [];
        foreach ($listSavedCoreCard as $savedCoreCard) {
            $customerObject = $customerRepository->findByMundipaggId(
                $savedCoreCard->getOwnerId()
            );

            $magento2SavedCardAdapter = new Magento2SavedCardAdapter($savedCoreCard);

            if(!is_null($customerObject)) {
                $magento2SavedCardAdapter->setCustomer($customerObject);
            }

            $cards[] = $magento2SavedCardAdapter;
        }

        return $cards;
    }

    /**
     * @return \MundiPagg\MundiPagg\Model\CardsRepository
     */
    public function getCardsRepository()
    {
        return $this->cardsRepository;
    }

    /**
     * @param mixed $cardsRepository
     *
     * @return self
     */
    public function setCardsRepository($cardsRepository)
    {
        $this->cardsRepository = $cardsRepository;

        return $this;
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param mixed $criteria
     *
     * @return self
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }
}
