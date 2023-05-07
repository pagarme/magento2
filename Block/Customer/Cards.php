<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Block\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Pagarme\Pagarme\Api\CustomerRepositoryInterface;
use Pagarme\Pagarme\Api\Data\CustomerInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use Pagarme\Pagarme\Api\SavedCardRepositoryInterface;
use Pagarme\Pagarme\Model\ResourceModel\SavedCard\Collection;
use Pagarme\Pagarme\Model\ResourceModel\SavedCard\CollectionFactoryInterface;

/**
 * Class Cards
 * @package Pagarme\Pagarme\Block\Customer
 */
class Cards extends Template
{
    /** @var string */
    protected $_template = 'Pagarme_Pagarme::customer/cards.phtml';

    /** @var Session */
    private $_customerSession;

    /** @var SavedCardRepositoryInterface */
    private $savedCardRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SearchCriteriaBuilder */
    private $_searchCriteriaBuilder;

    /** @var CollectionFactoryInterface */
    private $collectionFactory;

    /**
     * @var \Pagarme\Pagarme\Model\ResourceModel\SavedCard\Collection
     */
    protected $cards;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param SavedCardRepositoryInterface $savedCardRepository
     * @param Session $customerSession
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        SavedCardRepositoryInterface $savedCardRepository,
        Session $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->_customerSession = $customerSession;
        $this->savedCardRepository = $savedCardRepository;
        $this->customerRepository = $customerRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Cards'));
    }

    /**
     * @return false|Collection
     * @throws LocalizedException
     */
    public function getCards()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->cards) {
            $this->cards = $this->collectionFactory
                ->create(
                    $this->getCustomerPagarmeId()
                )->addFieldToSelect(
                    '*'
                )->setOrder(
                SavedCardInterface::CREATED_AT,
                'desc'
            );
        }
        return $this->cards;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCards()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'pagarme.customer.cards.pager'
            )->setCollection(
                $this->getCards()
            );
            $this->setChild('pager', $pager);
            $this->getCards()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function getCustomerPagarmeId()
    {
        if (!$pagarmeId = $this->_customerSession->getPagarmeId()) {
            $pagarmeCustomer = current(
                $this->customerRepository->getList(
                    $this->_searchCriteriaBuilder->addFilter(
                        CustomerInterface::CODE, $this->_customerSession->getCustomer()->getId()
                    )->create()
                )->getItems()
            );
            if ($pagarmeCustomer && $pagarmeCustomer instanceof CustomerInterface) {
                $pagarmeId = $pagarmeCustomer->getPagarmeId();
            }
        }
        return $pagarmeId;
    }

    /**
     * @param SavedCardInterface $card
     * @return string
     */
    public function getCardNumber(SavedCardInterface $card): string
    {
        return number_format(
            $card->getFirstSixDigits()/100,
            2,
            '.',
            ''
            ) . '**.****.' . $card->getLastFourDigits();
    }

    /**
     * @param SavedCardInterface $card
     * @return Phrase
     */
    public function getCardType(SavedCardInterface $card)
    {
        return __(ucwords(str_replace('_',' ', $card->getType())));
    }

    /**
     * @param SavedCardInterface $card
     * @return string
     */
    public function getRemoveUrl(SavedCardInterface $card): string
    {
        return $this->getUrl('pagarme/cards/remove', ['card_id' => $card->getId()]);
    }

    /**
     * Get customer account URL
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get message for no cards.
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyCardsMessage()
    {
        return __('You have saved no cards.');
    }
}
