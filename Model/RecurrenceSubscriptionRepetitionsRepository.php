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

use Magento\Framework\Api\SearchCriteriaInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsSearchResultsInterface;
use Pagarme\Pagarme\Api\RecurrenceSubscriptionRepetitionsRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterfaceFactory;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsSearchResultsInterfaceFactory;
use Pagarme\Pagarme\Model\ResourceModel\RecurrenceSubscriptionRepetitions as ResourceRecurrenceSubscriptionRepetitions;
use Pagarme\Pagarme\Model\ResourceModel\RecurrenceSubscriptionRepetitions\CollectionFactory as RecurrenceSubscriptionRepetitionsCollectionFactory;

/**
 * Class RecurrenceSubscriptionRepetitionsRepository
 * @package Pagarme\Pagarme\Model
 */
class RecurrenceSubscriptionRepetitionsRepository implements RecurrenceSubscriptionRepetitionsRepositoryInterface
{
    /**
     * @var RecurrenceSubscriptionRepetitionsInterfaceFactory
     */
    protected $recurrenceSubscriptionRepetitionsFactory;

    /**
     * @var RecurrenceSubscriptionRepetitionsCollectionFactory
     */
    protected $recurrenceSubscriptionRepetitionsCollectionFactory;

    /**
     * @var RecurrenceSubscriptionRepetitions
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceRecurrenceSubscriptionRepetitions
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceRecurrenceSubscriptionRepetitions $resource
     * @param RecurrenceSubscriptionRepetitionsInterfaceFactory $recurrenceSubscriptionRepetitionsFactory
     * @param RecurrenceSubscriptionRepetitionsCollectionFactory $recurrenceSubscriptionRepetitionsCollectionFactory
     * @param RecurrenceSubscriptionRepetitionsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceRecurrenceSubscriptionRepetitions $resource,
        RecurrenceSubscriptionRepetitionsInterfaceFactory $recurrenceSubscriptionRepetitionsFactory,
        RecurrenceSubscriptionRepetitionsCollectionFactory $recurrenceSubscriptionRepetitionsCollectionFactory,
        RecurrenceSubscriptionRepetitionsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->recurrenceSubscriptionRepetitionsFactory = $recurrenceSubscriptionRepetitionsFactory;
        $this->recurrenceSubscriptionRepetitionsCollectionFactory = $recurrenceSubscriptionRepetitionsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return RecurrenceSubscriptionRepetitionsInterface
     * @throws CouldNotSaveException
     */
    public function save(
        RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    ) {
        try {
            $this->resource->save($recurrenceSubscriptionRepetitions);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the recurrenceSubscriptionRepetitions: %1',
                $exception->getMessage()
            ));
        }
        return $recurrenceSubscriptionRepetitions;
    }

    /**
     * @param $recurrenceSubscriptionRepetitionsId
     * @return RecurrenceSubscriptionRepetitionsInterface
     * @throws NoSuchEntityException
     */
    public function get($recurrenceSubscriptionRepetitionsId)
    {
        $recurrenceSubscriptionRepetitions = $this->recurrenceSubscriptionRepetitionsFactory->create();
        $this->resource->load($recurrenceSubscriptionRepetitions, $recurrenceSubscriptionRepetitionsId);
        if (!$recurrenceSubscriptionRepetitions->getId()) {
            throw new NoSuchEntityException(__('Recurrence Subscription Repetitions with id "%1" does not exist.', $recurrenceSubscriptionRepetitionsId));
        }
        return $recurrenceSubscriptionRepetitions;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return RecurrenceSubscriptionRepetitionsSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->recurrenceSubscriptionRepetitionsCollectionFactory->create();

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
     * @param RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(
        RecurrenceSubscriptionRepetitionsInterface $recurrenceSubscriptionRepetitions
    ) {
        try {
            $recurrenceSubscriptionRepetitionsModel = $this->recurrenceSubscriptionRepetitionsFactory->create();
            $this->resource->load($recurrenceSubscriptionRepetitionsModel, $recurrenceSubscriptionRepetitions->getRecurrenceSubscriptionRepetitionsId());
            $this->resource->delete($recurrenceSubscriptionRepetitionsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Recurrence Subscription Repetitions: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $recurrenceSubscriptionRepetitionsId
     * @return true
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($recurrenceSubscriptionRepetitionsId)
    {
        return $this->delete($this->get($recurrenceSubscriptionRepetitionsId));
    }
}
