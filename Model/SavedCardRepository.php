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
use Magento\Framework\Api\SearchResultsInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use Pagarme\Pagarme\Api\SavedCardRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Api\Data\SavedCardInterfaceFactory;
use Pagarme\Pagarme\Api\Data\SavedCardSearchResultsInterfaceFactory;
use Pagarme\Pagarme\Model\ResourceModel\SavedCard as ResourceSavedCard;
use Pagarme\Pagarme\Model\ResourceModel\SavedCard\CollectionFactory as SavedCardCollectionFactory;

/**
 * Class SavedCardRepository
 * @package Pagarme\Pagarme\Model
 */
class SavedCardRepository implements SavedCardRepositoryInterface
{
    /**
     * @var SavedCardInterfaceFactory
     */
    protected $savedCardFactory;

    /**
     * @var SavedCard
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceSavedCard
     */
    protected $resource;

    /**
     * @var SavedCardCollectionFactory
     */
    protected $savedCardCollectionFactory;

    /**
     * @param ResourceSavedCard $resource
     * @param SavedCardInterfaceFactory $savedCardFactory
     * @param SavedCardCollectionFactory $savedCardCollectionFactory
     * @param SavedCardSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSavedCard $resource,
        SavedCardInterfaceFactory $savedCardFactory,
        SavedCardCollectionFactory $savedCardCollectionFactory,
        SavedCardSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->savedCardFactory = $savedCardFactory;
        $this->savedCardCollectionFactory = $savedCardCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param SavedCardInterface $entity
     * @return SavedCardInterface
     * @throws CouldNotSaveException
     */
    public function save(SavedCardInterface $entity): SavedCardInterface
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the entity: %1',
                $exception->getMessage()
            ));
        }
        return $entity;
    }

    /**
     * @param string $entityId
     * @return SavedCardInterface
     * @throws NoSuchEntityException
     */
    public function get(string $entityId): SavedCardInterface
    {
        $savedCard = $this->savedCardFactory->create();
        $this->resource->load($savedCard, $entityId);
        if (!$savedCard->getId()) {
            throw new NoSuchEntityException(__('Entity with id "%1" does not exist.', $entityId));
        }
        return $savedCard;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->savedCardCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param SavedCardInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SavedCardInterface $entity): bool
    {
        try {
            $savedCardModel = $this->savedCardFactory->create();
            $this->resource->load($savedCardModel, $entity->getId());
            $this->resource->delete($savedCardModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the entity: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param string $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(string $entityId): bool
    {
        return $this->delete($this->get($entityId));
    }
}
