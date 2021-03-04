<?php


namespace Pagarme\Pagarme\Model;

use Pagarme\Pagarme\Api\ChargesRepositoryInterface;
use Pagarme\Pagarme\Api\Data\ChargesSearchResultsInterfaceFactory;
use Pagarme\Pagarme\Api\Data\ChargesInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Pagarme\Pagarme\Model\ResourceModel\Charges as ResourceCharges;
use Pagarme\Pagarme\Model\ResourceModel\Charges\CollectionFactory as ChargesCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class ChargesRepository implements ChargesRepositoryInterface
{

    protected $resource;

    protected $chargesFactory;

    protected $chargesCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataChargesFactory;

    private $storeManager;


    /**
     * @param ResourceCharges $resource
     * @param ChargesFactory $chargesFactory
     * @param ChargesInterfaceFactory $dataChargesFactory
     * @param ChargesCollectionFactory $chargesCollectionFactory
     * @param ChargesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceCharges $resource,
        ChargesFactory $chargesFactory,
        ChargesInterfaceFactory $dataChargesFactory,
        ChargesCollectionFactory $chargesCollectionFactory,
        ChargesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->chargesFactory = $chargesFactory;
        $this->chargesCollectionFactory = $chargesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataChargesFactory = $dataChargesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Pagarme\Pagarme\Api\Data\ChargesInterface $charges
    ) {
        try {
            $charges->getResource()->save($charges);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the charges: %1',
                $exception->getMessage()
            ));
        }
        return $charges;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($chargesId)
    {
        $charges = $this->chargesFactory->create();
        $charges->getResource()->load($charges, $chargesId);
        if (!$charges->getId()) {
            throw new NoSuchEntityException(__('Charges with id "%1" does not exist.', $chargesId));
        }
        return $charges;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chargesCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Pagarme\Pagarme\Api\Data\ChargesInterface $charges
    ) {
        try {
            $charges->getResource()->delete($charges);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Charges: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($chargesId)
    {
        return $this->delete($this->getById($chargesId));
    }
}
