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
use Pagarme\Pagarme\Api\CustomerRepositoryInterface;
use Pagarme\Pagarme\Api\Data\CustomerInterface;
use Pagarme\Pagarme\Api\Data\CustomerInterfaceFactory;
use Pagarme\Pagarme\Api\Data\CustomerSearchResultsInterfaceFactory;
use Pagarme\Pagarme\Model\ResourceModel\Customer as ResourceCustomer;
use Pagarme\Pagarme\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class CustomerRepository
 * @package Pagarme\Pagarme\Model
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var ResourceCustomer
     */
    protected $resource;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var Customer
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceCustomer $resource
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomer $resource,
        CustomerInterfaceFactory $customerFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param CustomerInterface $entity
     * @return CustomerInterface
     * @throws CouldNotSaveException
     */
    public function save(CustomerInterface $entity): CustomerInterface
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customer: %1',
                $exception->getMessage()
            ));
        }
        return $entity;
    }

    /**
     * @param string $entityId
     * @return CustomerInterface
     * @throws NoSuchEntityException
     */
    public function get(string $entityId): CustomerInterface
    {
        $customer = $this->customerFactory->create();
        $this->resource->load($customer, $entityId);
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Customer with id "%1" does not exist.', $entityId));
        }
        return $customer;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->customerCollectionFactory->create();
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
     * @param CustomerInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CustomerInterface $entity): bool
    {
        try {
            $customerModel = $this->customerFactory->create();
            $this->resource->load($customerModel, $entity->getId()());
            $this->resource->delete($customerModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the customer: %1',
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
