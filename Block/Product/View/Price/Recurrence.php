<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Block\Product\View\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Pagarme\Api\Data\RecurrencePriceInterface;
use Pagarme\Pagarme\Api\Data\RecurrencePriceInterfaceFactory;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;
use Pagarme\Pagarme\Api\RecurrenceProductsSubscriptionRepositoryInterface;
use Pagarme\Pagarme\Api\RecurrenceSubscriptionRepetitionsRepositoryInterface;

/**
 * Class Recurrence
 * @package Pagarme\Pagarme\Block\Product\View\Price
 */
class Recurrence extends Template
{
    /** @var RecurrencePriceInterfaceFactory */
    private $recurrencePriceInterfaceFactory;

    /** @var RecurrenceProductsSubscriptionRepositoryInterface */
    private $recurrenceProductsSubscriptionRepository;

    /** @var RecurrenceSubscriptionRepetitionsRepositoryInterface */
    private $recurrenceSubscriptionRepetitionsRepository;

    /** @var SearchCriteriaBuilder */
    private $_searchCriteriaBuilder;

    /** @var Data */
    private $_priceHelper;

    /** @var ProductRepositoryInterface */
    private $_productRepository;

    /** @var ProductInterfaceFactory */
    private $_productFactory;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Data $priceHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurrenceProductsSubscriptionRepositoryInterface $recurrenceProductsSubscriptionRepository
     * @param RecurrenceSubscriptionRepetitionsRepositoryInterface $recurrenceSubscriptionRepetitionsRepository
     * @param RecurrencePriceInterfaceFactory $recurrencePriceInterfaceFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Data $priceHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecurrenceProductsSubscriptionRepositoryInterface $recurrenceProductsSubscriptionRepository,
        RecurrenceSubscriptionRepetitionsRepositoryInterface $recurrenceSubscriptionRepetitionsRepository,
        RecurrencePriceInterfaceFactory $recurrencePriceInterfaceFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->recurrencePriceInterfaceFactory = $recurrencePriceInterfaceFactory;
        $this->recurrenceProductsSubscriptionRepository = $recurrenceProductsSubscriptionRepository;
        $this->recurrenceSubscriptionRepetitionsRepository = $recurrenceSubscriptionRepetitionsRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_priceHelper = $priceHelper;
        $this->_productRepository = $productRepository;
        $this->_productFactory = $productFactory;
    }

    /**
     * @return RecurrencePriceInterface
     */
    public function getNewRecurrencePriceEntity(): RecurrencePriceInterface
    {
        return $this->recurrencePriceInterfaceFactory->create();
    }

    /**
     * @param ProductInterface $product
     * @return RecurrencePriceInterface|null
     * @throws LocalizedException
     */
    public function getRecurrencePrice(ProductInterface $product)
    {
        $entity = $this->getNewRecurrencePriceEntity();
        $subscriptionProduct = $this->getRecurrenceProductSubscription((int)$product->getId());
        if ($subscriptionProduct) {
            foreach ($this->getRepetitions($subscriptionProduct) as $repetition) {
                $recurrencePrice = $repetition->getRecurrencePrice();
                if (!$recurrencePrice) {
                    $recurrencePrice = ($product->getFinalPrice() * 100);
                }
                $price = $recurrencePrice / $repetition->getIntervalCount() / 100;
                if ($repetition->getInterval() == Repetition::INTERVAL_YEAR) {
                    $price = $recurrencePrice / (12 * $repetition->getIntervalCount());
                }
                if (!$entity->getPrice() || $price < $entity->getPrice()) {
                    $entity->setPrice($price)
                        ->setInterval($repetition->getInterval())
                        ->setIntervalCount((int)$repetition->getIntervalCount());
                }
                return $entity;
            }
        }
        return null;
    }

    /**
     * @param int $productId
     * @return false|RecurrenceProductsSubscriptionInterface
     * @throws LocalizedException
     */
    public function getRecurrenceProductSubscription(int $productId)
    {
        return current(
            $this->recurrenceProductsSubscriptionRepository->getList(
                $this->_searchCriteriaBuilder->addFilter('product_id', $productId)->create()
            )->getItems()
        );
    }

    /**
     * @param RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
     * @return RecurrenceSubscriptionRepetitionsInterface[]
     * @throws LocalizedException
     */
    public function getRepetitions(RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription)
    {
        return $this->recurrenceSubscriptionRepetitionsRepository->getList(
            $this->_searchCriteriaBuilder->addFilter('subscription_id', $recurrenceProductsSubscription->getId())->create()
        )->getItems();
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct(): ProductInterface
    {
        if ($this->hasData('product') && $this->getData('product') instanceof ProductInterface) {
            return $this->getData('product');
        }
        if ($this->_request->getParam('id') && $product = $this->_productRepository->getById($this->_request->getParam('id'))) {
            return $product;
        }
        return $this->_productFactory->create();
    }
}
