<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterfaceFactory;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionSearchResultsInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionSearchResultsInterfaceFactory;
use Pagarme\Pagarme\Api\RecurrenceProductsSubscriptionRepositoryInterface;
use Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription as ResourceRecurrenceProductsSubscription;
use Pagarme\Pagarme\Model\ResourceModel\RecurrenceProductsSubscription\CollectionFactory as RecurrenceProductsSubscriptionCollectionFactory;

/**
 * Class RecurrenceProductsSubscriptionRepository
 * @package Pagarme\Pagarme\Model
 */
class RecurrenceProductsSubscriptionRepository implements RecurrenceProductsSubscriptionRepositoryInterface
{
    /**
     * @var RecurrenceProductsSubscriptionInterfaceFactory
     */
    protected $recurrenceProductsSubscriptionFactory;

    /**
     * @var RecurrenceProductsSubscription
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceRecurrenceProductsSubscription
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var RecurrenceProductsSubscriptionCollectionFactory
     */
    protected $recurrenceProductsSubscriptionCollectionFactory;

    /**
     * @param ResourceRecurrenceProductsSubscription $resource
     * @param RecurrenceProductsSubscriptionInterfaceFactory $recurrenceProductsSubscriptionFactory
     * @param RecurrenceProductsSubscriptionCollectionFactory $recurrenceProductsSubscriptionCollectionFactory
     * @param RecurrenceProductsSubscriptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceRecurrenceProductsSubscription $resource,
        RecurrenceProductsSubscriptionInterfaceFactory $recurrenceProductsSubscriptionFactory,
        RecurrenceProductsSubscriptionCollectionFactory $recurrenceProductsSubscriptionCollectionFactory,
        RecurrenceProductsSubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->recurrenceProductsSubscriptionFactory = $recurrenceProductsSubscriptionFactory;
        $this->recurrenceProductsSubscriptionCollectionFactory = $recurrenceProductsSubscriptionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
     * @return RecurrenceProductsSubscriptionInterface
     * @throws CouldNotSaveException
     */
    public function save(
        RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
    ): RecurrenceProductsSubscriptionInterface
    {
        try {
            $this->resource->save($recurrenceProductsSubscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Recurrence Products Subscription: %1',
                $exception->getMessage()
            ));
        }
        return $recurrenceProductsSubscription;
    }

    /**
     * @param $recurrenceProductsSubscriptionId
     * @return RecurrenceProductsSubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function get($recurrenceProductsSubscriptionId): RecurrenceProductsSubscriptionInterface
    {
        $recurrenceProductsSubscription = $this->recurrenceProductsSubscriptionFactory->create();
        $this->resource->load($recurrenceProductsSubscription, $recurrenceProductsSubscriptionId);
        if (!$recurrenceProductsSubscription->getId()) {
            throw new NoSuchEntityException(__('Recurrence Products Subscription with id "%1" does not exist.', $recurrenceProductsSubscriptionId));
        }
        return $recurrenceProductsSubscription;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return RecurrenceProductsSubscriptionSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ): SearchResultsInterface
    {
        $collection = $this->recurrenceProductsSubscriptionCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(
        RecurrenceProductsSubscriptionInterface $recurrenceProductsSubscription
    ): bool
    {
        try {
            $recurrenceProductsSubscriptionModel = $this->recurrenceProductsSubscriptionFactory->create();
            $this->resource->load($recurrenceProductsSubscriptionModel, $recurrenceProductsSubscription->getRecurrenceProductsSubscriptionId());
            $this->resource->delete($recurrenceProductsSubscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Recurrence Products Subscription: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $recurrenceProductsSubscriptionId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($recurrenceProductsSubscriptionId): bool
    {
        return $this->delete($this->get($recurrenceProductsSubscriptionId));
    }
}
